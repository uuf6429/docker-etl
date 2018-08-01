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
     * @var PathMarker
     */
    private $pathMarker;

    /**
     * @var Container
     */
    private $origContainer;

    /**
     * @param Container $container
     * @return MarkerProxy|Container
     */
    private function wrapContainer(Container $container)
    {
        $this->pathMarker = new PathMarker();
        $proxy = new MarkerProxy($container, $this->pathMarker);
        $this->pathMarker->mapToPaths($container);

        return $proxy;
    }

    private function checkUnusedPaths()
    {
        if ($this->pathMarker->hasUnmarkedPaths()) {
            $this->logger->warning(
                sprintf(
                    'Some configuration was not used by %s: %s',
                    get_class($this),
                    implode(', ', $this->pathMarker->getUniqueUnmarkedPaths())
                )
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function execute(Container $container, $value)
    {
        try {
            $this->origContainer = $container;
            $proxy = $this->wrapContainer($container);
            $this->doExecute($proxy, $value);
        } finally {
            $this->checkUnusedPaths();
        }
    }

    /**
     * @param MarkerProxy|Container $container
     * @param null|string $value
     */
    abstract protected function doExecute($container, $value);
}
