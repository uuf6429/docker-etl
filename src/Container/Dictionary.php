<?php

namespace uuf6429\DockerEtl\Container;

class Dictionary implements \ArrayAccess, \Countable
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

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->data);
    }
}
