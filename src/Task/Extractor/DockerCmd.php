<?php

namespace uuf6429\DockerEtl\Task\Extractor;

use uuf6429\DockerEtl\Task\Task;

class DockerCmd extends Task
{
    /**
     * @inheritdoc
     */
    public function getTaskOptionName()
    {
        return '--extract-from-docker-cmd';
    }

    /**
     * @inheritdoc
     */
    public function getTaskOptionMode()
    {
        return self::VALUE_REQUIRED;
    }

    /**
     * @inheritdoc
     */
    public function getTaskOptionDescription()
    {
        return 'Extracts container configuration by running <comment>docker inspect</comment>. Value must be the container id or name.';
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        // TODO: Implement execute() method.
    }
}
