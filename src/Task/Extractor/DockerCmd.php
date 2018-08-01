<?php

namespace uuf6429\DockerEtl\Task\Extractor;

use JsonSchema\Exception\JsonDecodingException;
use Symfony\Component\Process\Process;
use uuf6429\DockerEtl\Container\Container;

class DockerCmd extends Extractor
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
        return 'Extracts container configuration by running <comment>docker inspect</comment>.'
            . "\nValue must be the container id or name.";
    }

    /**
     * @inheritdoc
     */
    protected function extract($value)
    {
        $process = new Process(['docker', 'inspect', $value]);
        $process->mustRun();
        $output = $process->getOutput();
        $config = @json_decode($output);

        if (($error = json_last_error()) !== JSON_ERROR_NONE) {
            throw new JsonDecodingException($error);
        }

        $config = $config[0];
        unset(
            $config->Id,
            $config->Created,
            $config->State,
            $config->RestartCount
        );

        return $config;
    }

    /**
     * @inheritdoc
     */
    protected function process(Container $container, $extractedConfig)
    {
        $container->name = ltrim($extractedConfig->Name, '/');
        $container->entrypoint = $extractedConfig->Config->Entrypoint;
        $container->cmd = $extractedConfig->Config->Cmd;
    }
}
