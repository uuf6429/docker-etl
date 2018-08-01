<?php

namespace uuf6429\DockerEtl\PathMarker;

class MarkerProxy implements PropertyAccess, \ArrayAccess, \Countable
{
    /**
     * @var object|array
     */
    protected $target;

    /**
     * @var PathMarker
     */
    protected $pathMarker;

    /**
     * @var string
     */
    protected $currentPath;

    /**
     * @param object|array $target
     * @param PathMarker $pathMarker
     * @param string $currentPath
     */
    public function __construct($target, $pathMarker, $currentPath = '')
    {
        $this->target = $target;
        $this->pathMarker = $pathMarker;
        $this->currentPath = $currentPath;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        $this->markArrayPath($offset);

        return array_key_exists($offset, $this->target);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->wrapValue(
            $this->target[$offset],
            $this->buildArrayPath($offset)
        );
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->markArrayPath($offset);

        $this->target[$offset] = $value;
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        unset($this->target[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->target);
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        return $this->wrapValue(
            $this->target->$name,
            $this->buildObjectPath($name)
        );
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        $this->markObjectPath($name);

        $this->target->$name = $value;
    }

    /**
     * @inheritdoc
     */
    public function __isset($name)
    {
        $this->markObjectPath($name);

        return isset($this->target->$name);
    }

    /**
     * @inheritdoc
     */
    public function __unset($name)
    {
        unset($this->target->$name);
    }

    /**
     * @param mixed $value
     * @param string $path
     * @return mixed
     */
    private function wrapValue($value, $path)
    {
        if ($this->isProxyable($value)) {
            return new self($value, $this->pathMarker, $path);
        }

        $this->pathMarker->markPath($path);

        return $value;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    private function isProxyable($value)
    {
        if (is_object($value) && !$value instanceof self) {
            return true;
        }

        if (is_array($value)) {
            foreach ($value as $subValue) {
                if ($this->isProxyable($subValue)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param string $prop
     * @return string
     */
    private function buildObjectPath($prop)
    {
        return $this->currentPath ? "{$this->currentPath}.{$prop}" : $prop;
    }

    /**
     * @param string $index
     * @return string
     */
    private function buildArrayPath($index)
    {
        return "{$this->currentPath}[{$index}]";
    }

    /**
     * @param string $prop
     */
    private function markObjectPath($prop)
    {
        $this->pathMarker->markPath($this->buildObjectPath($prop));
    }

    /**
     * @param string $index
     */
    private function markArrayPath($index)
    {
        $this->pathMarker->markPath($this->buildArrayPath($index));
    }
}
