<?php

namespace uuf6429\DockerEtl\PathMarker;

interface PropertyAccess
{
    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name);

    /**
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function __set($name, $value);

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name);

    /**
     * @param string $name
     */
    public function __unset($name);
}
