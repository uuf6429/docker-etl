<?php

namespace uuf6429\DockerEtl\Task\Loader;

use uuf6429\DockerEtl\Container\Container;

class DockerCmd extends Loader
{
    /**
     * @inheritdoc
     */
    public function getTaskOptionName()
    {
        return '--load-into-docker-cmd';
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
        return 'Loads container configuration into the specified file.'
            . "\nThe file must start with the write mode; \">\" to overwrite or \">>\" to append. Examples:"
            . "\n- <info>--load-into-docker-cmd=>>php://stdout</info> - prints to standard output"
            . "\n- <info>--load-into-docker-cmd=>/etc/rc.local</info> - overwrites the specified script"
            . "\nNote: When appending, the file should already end with a new line.";
    }

    /**
     * @inheritdoc
     */
    protected function load($container, $value)
    {
        if (strpos($value, '>>') === 0) {
            $handle = fopen(substr($value, 2), 'ab');
        } elseif (strpos($value, '>') === 0) {
            $handle = fopen(substr($value, 1), 'wb');
        } else {
            throw new \InvalidArgumentException('File mode was not specified (">" or ">>" was expected).');
        }

        fwrite($handle, $this->generateCommandLine($container) . PHP_EOL);
        fclose($handle);
    }

    /**
     * @param Container $container
     * @return string
     */
    private function generateCommandLine($container)
    {
        $cmd = ['docker', 'run'];

        // add options
        if ($container->name) {
            $cmd[] = '--name';
            $cmd[] = $container->name;
        }
        if ($container->entrypoint) {
            $cmd[] = '--entrypoint';
            $cmd[] = $container->entrypoint;
        }
        // TODO add other options

        // add image
        if ($container->image) {
            $cmd[] = $container->image;
        }

        // add cmd+args
        if ($container->cmd) {
            $cmd = array_merge($cmd, $container->cmd);
        }

        return implode(' ', $this->escapeCommandLine($cmd));
    }

    /**
     * @param string[] $cli
     * @return string[]
     */
    private function escapeCommandLine(array $cli)
    {
        return array_map(
            function ($arg) {
                return preg_match('/[^a-zA-Z\\d]/', $arg)
                    ? escapeshellarg($arg) : $arg;
            },
            $cli
        );
    }
}
