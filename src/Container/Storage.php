<?php

namespace uuf6429\DockerEtl\Container;

abstract class Storage
{
    /**
     * @var bool
     */
    public $isWritable;

    /**
     * @var string
     */
    public $source;

    /**
     * @var string
     */
    public $destination;
}
