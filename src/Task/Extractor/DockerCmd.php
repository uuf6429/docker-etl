<?php

namespace uuf6429\DockerEtl\Task\Extractor;

use JsonSchema\Exception\JsonDecodingException;
use Symfony\Component\Process\Process;
use uuf6429\DockerEtl\Container\BindStorage;
use uuf6429\DockerEtl\Container\Container;
use uuf6429\DockerEtl\Container\Storage;
use uuf6429\DockerEtl\Container\VolumeStorage;

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

        // focus on first item in config
        $config = $config[0];

        // removing uninteresting parts
        unset(
            $config->Id,
            $config->Created,
            $config->State,
            $config->RestartCount,
            $config->Config->Volumes
        );

        return $config;
    }

    /**
     * @inheritdoc
     */
    protected function process(Container $container, $extractedConfig)
    {
        $container->cmd = $extractedConfig->Config->Cmd;
        $container->entrypoint = $extractedConfig->Config->Entrypoint;
        array_map([$container->environment, 'addFromString'], (array)$extractedConfig->Config->Env);
        $container->image = $extractedConfig->Config->Image;
        $container->labels->addFromArray((array)$extractedConfig->Config->Labels);
        foreach ($extractedConfig->Mounts as $mount) {
            if (($storage = $this->processMount($mount)) !== null) {
                $container->volumes->add($storage);
            } else {
                $this->logger->warning('Volume type or configuration not supported: ' . json_encode($mount));
            }
        }
        $container->name = ltrim($extractedConfig->Name, '/');
        $container->workingDir = $extractedConfig->Config->WorkingDir;
    }

    /**
     * @param object $mount
     * @return null|Storage
     */
    private function processMount($mount)
    {
        $result = null;

        switch ($mount->Type) {
            case 'bind':
                $result = new BindStorage();
                $result->source = $mount->Source;
                $result->destination = $mount->Destination;
                $result->isWritable = $mount->RW; // TODO ensure this contains a bool, not a string
                // TODO use more fields
                break;
            case 'volume':
                $result = new VolumeStorage();
                $result->source = $mount->Source;
                $result->destination = $mount->Destination;
                $result->isWritable = $mount->RW; // TODO ensure this contains a bool, not a string
                // TODO use more fields
                break;
        }

        return $result;
    }
}
