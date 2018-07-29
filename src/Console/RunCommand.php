<?php

namespace uuf6429\DockerEtl\Console;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use uuf6429\DockerEtl\Container;
use uuf6429\DockerEtl\Task\Task;

class RunCommand extends Command
{
    /**
     * @var Task[] Array key is the task option name.
     */
    private $tasks;

    /**
     * @var array[] $taskOptions Array of arrays (0 => task option name, 1 => task option value).
     */
    private $taskOptions;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Application $application
     * @param Task[] $tasks Array key is the task option name.
     * @param array[] $taskOptions Array of arrays (0 => task option name, 1 => task option value).
     * @param LoggerInterface $logger
     */
    public function __construct(Application $application, array $tasks, array $taskOptions, LoggerInterface $logger)
    {
        $this->setApplication($application);
        $this->tasks = $tasks;
        $this->taskOptions = $taskOptions;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Extracts, Transforms and Loads a docker container configuration.');

        foreach ($this->tasks as $task) {
            $this->addOption($task->getTaskOptionName(), null, $task->getTaskOptionMode(), $task->getTaskOptionDescription());
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = new Container\State();
        foreach ($this->taskOptions as list($key, $val)) {
            try {
                $this->logger->debug("Applying task $key=$val ...");
                $this->tasks[$key]->execute($container, $val);
            } catch (\Exception $ex) {
                throw new \InvalidArgumentException("Failed for \"{$key}={$val}\": {$ex->getMessage()}", 0, $ex);
            }
        }
    }
}
