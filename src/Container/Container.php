<?php

namespace uuf6429\DockerEtl\Container;

class Container
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
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
     * @var string
     */
    public $entrypoint;

    /**
     * @var string[]
     */
    public $cmd;

    /**
     * @var StorageCollection|Storage[]
     */
    public $volumes;

    /**
     * @var string
     */
    public $workingDir;

    public function __construct()
    {
        $this->reset();
    }

    public function reset()
    {
        $this->name = '';
        $this->image = '';
        $this->labels = new Dictionary();
        $this->environment = new Dictionary();
        $this->entrypoint = '';
        $this->cmd = [];
        $this->volumes = new StorageCollection();
        $this->workingDir = '';
    }
}
