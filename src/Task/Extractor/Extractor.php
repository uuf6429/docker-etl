<?php

namespace uuf6429\DockerEtl\Task\Extractor;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use uuf6429\DockerEtl\Container\Container;
use uuf6429\DockerEtl\PathMarker\MarkerProxy;
use uuf6429\DockerEtl\PathMarker\PathMarker;
use uuf6429\DockerEtl\Task\Task;

abstract class Extractor extends Task implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @inheritdoc
     */
    public function execute(Container $container, $value)
    {
        $pathMarker = new PathMarker();

        try {
            $rawConfig = $this->extract($value);
            $proxy = new MarkerProxy($rawConfig, $pathMarker);
            $pathMarker->mapToPaths($rawConfig);
            $this->process($container, $proxy);
        } finally {
            if ($pathMarker->hasUnmarkedPaths()) {
                $this->logger->warning(
                    sprintf(
                        'Some configuration was not used by %s: %s',
                        get_class($this),
                        implode(', ', $pathMarker->getUniqueUnmarkedPaths())
                    )
                );
            }
        }
    }

    /**
     * @param null|string $value
     * @return object
     */
    abstract protected function extract($value);

    /**
     * @param Container $container
     * @param MarkerProxy|object $extractedConfig
     */
    abstract protected function process(Container $container, $extractedConfig);
}
