<?php

namespace uuf6429\DockerEtl\Container;

class StorageCollection implements \ArrayAccess, \Countable
{
    /**
     * @var Storage[]
     */
    protected $volumes = [];

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->volumes);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->volumes[$offset];
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if (!$value instanceof Storage) {
            throw new \InvalidArgumentException('Only storage objects can be added to storage collection.');
        }

        $this->volumes[$offset] = $value;
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        unset($this->volumes[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->volumes);
    }

    /**
     * @param Storage $value
     */
    public function add(Storage $value)
    {
        $this->volumes[] = $value;
    }
}
