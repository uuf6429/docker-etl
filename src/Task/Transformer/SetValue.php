<?php

namespace uuf6429\DockerEtl\Task\Transformer;

use uuf6429\DockerEtl\Container\State;
use uuf6429\DockerEtl\Task\Task;

class SetValue extends Task
{
    /**
     * @inheritdoc
     */
    public function getTaskOptionName()
    {
        return '--set';
    }

    /**
     * @inheritdoc
     */
    public function getTaskOptionMode()
    {
        return self::VALUE_OPTIONAL;
    }

    /**
     * @inheritdoc
     */
    public function getTaskOptionDescription()
    {
        return 'Sets the value of a setting. Examples:'
            . "\n- <info>--set=image=php:7-cli-alpine</info>"
            . "\n- <info>'--set=labels.MYLABEL=MY VALUE'</info>";
    }

    /**
     * @inheritdoc
     */
    public function execute(State $container, $value)
    {
        list($path, $val) = explode('=', $value, 2);

        $var = &$container;
        $parts = explode('.', $path);
        while (($part = array_shift($parts)) !== null) {
            if (is_object($var)) {
                $var = &$var->$part;
            } elseif (is_array($var)) {
                $var = &$var[$part];
            } else {
                throw new \InvalidArgumentException("Cannot set $path; $part is not an array or object.");
            }
        }
        $var = $val;
    }
}
