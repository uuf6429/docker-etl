<?php

namespace uuf6429\DockerEtl\Task\Loader;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use uuf6429\DockerEtl\Container\Container;
use uuf6429\DockerEtl\PathMarker\MarkerProxy;
use uuf6429\DockerEtl\PathMarker\PathMarker;
use uuf6429\DockerEtl\Task\Task;

abstract class Loader extends Task implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @inheritdoc
     */
    final public function execute(Container $container, $value)
    {
        $pathMarker = new PathMarker();

        try {
            $proxy = new MarkerProxy($container, $pathMarker);
            $pathMarker->mapToPaths($container);
            $this->load($proxy, $value);
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
     * @param MarkerProxy|Container $container
     * @param null|string $value
     */
    abstract protected function load($container, $value);
}
