<?php

namespace uuf6429\DockerEtl\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use uuf6429\DockerEtl\Task\Task;

class RunCommand extends Command
{
    /**
     * @var Task[] Array key is the task option name.
     */
    private $tasks;

    /**
     * @param Application $application
     * @param Task[] $tasks Array key is the task option name.
     */
    public function __construct(Application $application, array $tasks)
    {
        $this->setApplication($application);
        $this->tasks = $tasks;

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
        if (!$input instanceof SequentialArgvInput) {
            throw new \RuntimeException('SequentialArgvInput expected.');
        }

        foreach ($input->getParsedOptions() as list($key, $val)) {

        }
    }
}
