<?php

namespace uuf6429\DockerEtl\Task;

use uuf6429\DockerEtl\Container\Container;

class Reset extends Task
{
    /**
     * @inheritdoc
     */
    public function getTaskOptionName()
    {
        return '--reset';
    }

    /**
     * @inheritdoc
     */
    public function getTaskOptionMode()
    {
        return self::VALUE_NONE;
    }

    /**
     * @inheritdoc
     */
    public function getTaskOptionDescription()
    {
        return 'Resets container configuration to defaults.';
    }

    /**
     * @inheritdoc
     */
    public function execute(Container $container, $value)
    {
        $container->reset();
    }
}
