<?php

namespace uuf6429\DockerEtl\Task\Extractor;

use uuf6429\DockerEtl\Container\State;
use uuf6429\DockerEtl\Task\Task;

class DockerApi extends Task
{
    /**
     * @inheritdoc
     */
    public function getTaskOptionName()
    {
        return '--extract-from-docker-api';
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
        return 'Extracts container configuration by inspected container through REST API. Value must be the container id.';
    }

    /**
     * @inheritdoc
     */
    public function execute(State $container, $value)
    {
        // TODO: Implement execute() method.
    }
}
