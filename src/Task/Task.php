<?php

namespace uuf6429\DockerEtl\Task;

use Symfony\Component\Console\Input\InputOption;
use uuf6429\DockerEtl\Container\State;

abstract class Task
{
    const VALUE_NONE = InputOption::VALUE_NONE;
    const VALUE_REQUIRED = InputOption::VALUE_REQUIRED;
    const VALUE_OPTIONAL = InputOption::VALUE_OPTIONAL;

    /**
     * @return string
     */
    abstract public function getTaskOptionName();

    /**
     * @return integer One of self::VALUE_* constants.
     */
    abstract public function getTaskOptionMode();

    /**
     * @return string
     */
    abstract public function getTaskOptionDescription();

    /**
     * @param State $container
     * @param null|string $value
     */
    abstract public function execute(State $container, $value);
}
