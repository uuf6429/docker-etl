<?php

namespace uuf6429\DockerEtl\Console;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use uuf6429\DockerEtl\Task;

class Application extends \Symfony\Component\Console\Application
{
    /**
     * @var Task\Task[]
     */
    private $tasks = [];

    /**
     * @param string $version
     */
    public function __construct($version)
    {
        parent::__construct('docker-etl', $version);

        date_default_timezone_set(@date_default_timezone_get());

        $this->addTasks($this->getDefaultTasks());
        $this->setDefaultCommand('run');
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();

        $options = $definition->getOptions();
        foreach ($options as $k => $option) {
            if ($option->getName() === 'no-interaction') {
                unset($options[$k]);
            }
        }

        $options[] = new InputOption(
            'include',
            null,
            InputOption::VALUE_REQUIRED,
            sprintf(
                'Extend %s by including one or more PHP files (separated by a "%s").',
                $this->getName(),
                PATH_SEPARATOR
            )
        );

        $definition->setOptions($options);

        return $definition;
    }

    /**
     * @return Task\Task[]
     */
    private function getDefaultTasks()
    {
        return [
            new Task\Reset(),
            new Task\Extractor\DockerCmd(),
            new Task\Extractor\DockerApi(),
            new Task\Extractor\DockerCompose(),
            new Task\Transformer\SetValue(),
            new Task\Loader\DockerCmd(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        if ($input === null) {
            $input = new ArgvInput();
        }

        if ($output === null) {
            $output = new ConsoleOutput();
        }

        $logger = $output instanceof ConsoleOutputInterface
            ? new ConsoleLogger($output->getErrorOutput())
            : new NullLogger();

        $input->setInteractive(false);

        $includes = explode(PATH_SEPARATOR, $input->getParameterOption(['--include'], ''));
        foreach (array_filter($includes) as $include) {
            /** @noinspection PhpIncludeInspection */
            include_once $include;
        }

        $this->configureTasks($logger, $output);

        $taskOptions = $this->parseTaskOptions(array_keys($this->tasks));

        $this->addCommands([
            new RunCommand($this, $this->tasks, $taskOptions, $logger),
            new UpdateCommand($this),
        ]);

        if (!$input->getFirstArgument()) {
            $cmd = $taskOptions ? ['run'] : ['help', 'run'];
            $input = new ArgvInput(array_merge($_SERVER['argv'], $cmd));
        }

        return parent::run($input, $output);
    }

    /**
     * @param Task\Task[] $tasks
     */
    public function addTasks(array $tasks)
    {
        $this->tasks = array_merge($this->tasks, $tasks);
    }

    /**
     * @return Task\Task[]
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @param LoggerInterface $logger
     * @param OutputInterface $output
     */
    private function configureTasks(LoggerInterface $logger, OutputInterface $output)
    {
        $tasks = $this->tasks;
        $this->tasks = [];

        foreach ($tasks as $task) {
            if ($task instanceof LoggerAwareInterface) {
                $task->setLogger($logger);
            }
            if ($task instanceof ApplicationAwareInterface) {
                $task->setApplication($this);
            }
            if ($task instanceof OutputAwareInterface) {
                $task->setOutput($output);
            }

            $taskOptionName = $task->getTaskOptionName();
            if (array_key_exists($taskOptionName, $this->tasks)) {
                $logger->warning(sprintf(
                    'Task with key %s already exists and will be overwritten (old task class: %s, new task class: %s).',
                    $taskOptionName,
                    get_class($this->tasks[$taskOptionName]),
                    get_class($task)
                ));
            }
            $this->tasks[$taskOptionName] = $task;
        }
    }

    /**
     * @param string[] $taskOptionNames
     *
     * @return array[]
     */
    private function parseTaskOptions(array $taskOptionNames)
    {
        $parsed = [];
        $tokens = array_slice($_SERVER['argv'], 1);

        foreach ($tokens as $token) {
            if (preg_match('/^(--[^=]+)(?:=(.*))?$/', $token, $matches)
                && in_array($matches[1], $taskOptionNames, true)
            ) {
                $parsed[] = [$matches[1], isset($matches[2]) ? $matches[2] : null];
            }
        }

        return $parsed;
    }
}
