<?php

namespace uuf6429\DockerEtl\Task\Loader;

use uuf6429\DockerEtl\Container\State;
use uuf6429\DockerEtl\Task\Task;

class DockerCmd extends Task
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
    public function execute(State $container, $value)
    {
        if (strpos($value, '>>') === 0) {
            $handle = fopen(substr($value, 2), 'ab');
        } elseif (strpos($value, '>') === 0) {
            $handle = fopen(substr($value, 1), 'wb');
        } else {
            throw new \InvalidArgumentException('File mode was not specified (">" or ">>" was expected).');
        }

        fwrite($handle, $this->generateCommandLine() . PHP_EOL);
        fclose($handle);
    }

    private function generateCommandLine()
    {
        return 'docker run TODO';
    }
}
