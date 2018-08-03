<?php

namespace uuf6429\DockerEtl\Container;

class Dictionary implements \ArrayAccess, \Countable
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param string $key
     * @param mixed $value
     */
    public function add($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @param string $string
     */
    public function addFromString($string)
    {
        $parsed = explode('=', $string, 2);
        $this->add($parsed[0], isset($parsed[1]) ? $parsed[1] : '');
    }

    /**
     * @param array $array
     */
    public function addFromArray(array $array)
    {
        $this->data = array_merge($this->data, $array);
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
