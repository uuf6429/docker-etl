<?php

namespace uuf6429\DockerEtl\Container;

class State
{
    /**
     * @var null|string
     */
    protected $name;

    /**
     * @var array
     */
    protected $labels = [];

    /**
     * @var array
     */
    protected $volumes = [];

    public function reset()
    {
        $this->name = null;
        $this->labels = [];
        $this->volumes = [];
    }
}
