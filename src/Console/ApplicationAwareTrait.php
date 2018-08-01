<?php

namespace uuf6429\DockerEtl\Console;

/**
 * Basic Implementation of ApplicationAwareInterface.
 */
trait ApplicationAwareTrait
{
    /**
     * The application instance.
     *
     * @var Application
     */
    protected $application;

    /**
     * Sets the application.
     *
     * @param Application $application
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;
    }
}
