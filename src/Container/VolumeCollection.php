<?php

namespace uuf6429\DockerEtl\Container;

class VolumeCollection implements \ArrayAccess, \Countable
{
    /**
     * @var Volume[]
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
        if (!isset($this->volumes[$offset])) {
            $this->volumes[$offset] = new Volume();
        }

        return $this->volumes[$offset];
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if (!$value instanceof Volume) {
            throw new \InvalidArgumentException('Only volumes can be added to volume collection.');
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
}
