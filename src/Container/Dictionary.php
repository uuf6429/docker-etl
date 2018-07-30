<?php

namespace uuf6429\DockerEtl\Container;

class Dictionary
{
    /**
     * @var array
     */
    protected $data = [];

    public function add($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function addFromString($string)
    {
        $parsed = explode('=', $string, 2);
        $this->add($parsed[0], isset($parsed[1]) ? $parsed[1] : '');
    }
}
