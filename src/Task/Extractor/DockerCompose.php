<?php

namespace uuf6429\DockerEtl\Task\Extractor;

use uuf6429\DockerEtl\Container\State;
use uuf6429\DockerEtl\Task\Task;

class DockerCompose extends Task
{
    /**
     * @inheritdoc
     */
    public function getTaskOptionName()
    {
        return '--extract-from-docker-compose';
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
        return 'Extracts container configuration from a Docker Compose file.'
            . "\nValue must be a path/url to the file followed with a colon and the desired service key. Examples:"
            . "\n- <info>https://raw.githubusercontent.com/dockersamples/example-voting-app/master/docker-compose.yml:vote</info>"
            . "\n- <info>C:\\Projects\\test\\docker-compose.yml:app</info>"
            . "\n- <info>/home/pandora/projects/acme/docker-compose.yml:server</info>";
    }

    /**
     * @inheritdoc
     */
    public function execute(State $container, $value)
    {
        // TODO: Implement execute() method.
    }
}
