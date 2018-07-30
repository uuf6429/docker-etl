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
     * @var Dictionary
     */
    public $labels;

    /**
     * @var Dictionary
     */
    public $environment;

    /**
     * @var null|string|string[]
     */
    public $entrypoint;

    /**
     * @var null|string|string[]
     */
    public $cmd;

    /**
     * @var VolumeCollection|Volume[]
     */
    public $volumes;

    public function __construct()
    {
        $this->reset();
    }

    public function reset()
    {
        $this->name = null;
        $this->image = null;
        $this->labels = new Dictionary();
        $this->environment = new Dictionary();
        $this->volumes = new VolumeCollection();
    }
}
