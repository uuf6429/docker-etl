<?php

namespace uuf6429\DockerEtl\Container;

class State
{
    /**
     * @var null|string
     */
    public $name;

    /**
     * @var null|string
     */
    public $image;

    /**
     * @var array
     */
    public $labels = [];

    /**
     * @var array
     */
    protected $volumes = [];

    public function reset()
    {
        $this->name = null;
        $this->image = null;
        $this->labels = [];
        $this->volumes = [];
    }
}
