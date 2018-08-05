<?php

namespace uuf6429\DockerEtl\Task\Extractor;

use Symfony\Component\Yaml\Yaml;
use uuf6429\DockerEtl\Container\Container;
use uuf6429\DockerEtl\PathMarker\MarkerProxy;
use uuf6429\DockerEtl\Task\Task;

class DockerCompose extends Extractor
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
    protected function extract($value)
    {
        list($path, $name) = $this->parseValue($value);

        $config = Yaml::parseFile($path, Yaml::PARSE_OBJECT_FOR_MAP);

        if (!is_object($config) || empty($config->services)) {
            throw new \RuntimeException('Invalid file structure or no services defined.');
        }

        if (!isset($config->services->$name)) {
            throw new \RuntimeException(sprintf(
                'Service key "%s" not found (available service keys: %s).',
                $name,
                implode(', ', array_keys(get_object_vars($config->services)))
            ));
        }

        // focus on the service we're interested in
        $config->service = $config->services->$name;

        // removing uninteresting parts
        unset($config->version, $config->services);

        return $config;
    }

    /**
     * @inheritdoc
     */
    protected function process(Container $container, $extractedConfig)
    {
        if (!empty($extractedConfig->service->build)) {
            $this->logger->warning('The service builds an image and therefore the image tag cannot be determined.');
        } else {
            $container->image = $extractedConfig->service->image;
        }

        if (!empty($extractedConfig->service->command)) {
            $container->cmd = $this->parseCommand($extractedConfig->service->command);
        }

        // TODO handle ports
        // TODO handle volumes

        if (!empty($extractedConfig->service->environment)) {
            if (is_array($extractedConfig->service->environment)) {
                array_map([$container->environment, 'addFromString'], $extractedConfig->service->environment);
            } elseif (is_object($extractedConfig->service->environment)) {
                $container->environment->addFromArray(get_object_vars($extractedConfig->service->environment));
            }
        }
    }

    /**
     * @param string $value
     * @return string[]
     */
    private function parseValue($value)
    {
        $parts = array_reverse(array_map('strrev', explode(':', strrev($value), 2)));

        if (count($parts) !== 2) {
            throw new \InvalidArgumentException('Argument must be a path/url followed by a colon and the service key.');
        }

        return $parts;
    }

    /**
     * @param string|string[] $cmd
     * @return string[]
     */
    private function parseCommand($cmd)
    {
        if (is_array($cmd)) {
            return $cmd;
        }

        if (trim($cmd) === '') {
            return [];
        }

        return \Clue\Arguments\split($cmd);
    }
}
