<?php

namespace uuf6429\DockerEtl\Task\Extractor;

use uuf6429\DockerEtl\Container\Container;
use uuf6429\DockerEtl\Task\Task;

/**
 * @todo
 * @see https://github.com/docker/compose/blob/b2cc8a290afa937aa949047827ce44d77085ebec/compose/config/config_schema_v3.0.json
 */
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
    public function execute(Container $container, $value)
    {
        // TODO: Implement execute() method.
    }
}
