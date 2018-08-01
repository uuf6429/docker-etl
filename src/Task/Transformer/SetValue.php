<?php

namespace uuf6429\DockerEtl\Task\Transformer;

use uuf6429\DockerEtl\Container\Container;
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
    public function execute(Container $container, $value)
    {
        list($path, $val) = explode('=', $value, 2);

        $var = &$container;
        $previousPart = null;
        foreach ($this->parsePath($path) as $part) {
            switch ($part['type']) {
                case 'o':
                    if (!is_object($var)) {
                        throw new \InvalidArgumentException("Cannot set {$path}; {$previousPart['name']} is not an object.");
                    }
                    if (!property_exists($var, $part['name'])) {
                        throw new \InvalidArgumentException("Cannot set {$path}; property {$part['name']} does not seem to exist.");
                    }
                    $var = &$var->{$part['name']};
                    break;
                case 'a':
                    if (!is_array($var) && !$var instanceof \ArrayAccess) {
                        throw new \InvalidArgumentException("Cannot set {$path}; {$previousPart['name']} cannot be accessed as an array.");
                    }
                    $var = &$var[$part['name']];
                    break;
            }
            $previousPart = $part;
        }
        $var = json_decode($val);
    }

    /**
     * @param string $path
     * @return array
     */
    private function parsePath($path)
    {
        $emptyPart = [
            'name' => '',
            'type' => 'o',
        ];
        $part = $emptyPart;
        $parts = [];
        $inArray = false;
        foreach (str_split($path) as $char) {
            if ($inArray && $char === ']') {
                $inArray = false;
                $parts[] = $part;
                $part = $emptyPart;
                continue;
            }
            if (!$inArray && $char === '[') {
                $inArray = true;
                if ($part['name'] !== '') {
                    $parts[] = $part;
                }
                $part = $emptyPart;
                $part['type'] = 'a';
                continue;
            }
            if (!$inArray && $char === '.') {
                if ($part['name'] !== '') {
                    $parts[] = $part;
                }
                $part = $emptyPart;
                continue;
            }

            $part['name'] .= $char;
        }

        if ($part && $part['name'] !== '') {
            $parts[] = $part;
        }

        return $parts;
    }
}
