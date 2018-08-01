<?php

namespace uuf6429\DockerEtl\Console;

/**
 * Describes an application-aware instance.
 */
interface ApplicationAwareInterface
{
    /**
     * Sets an application instance on the object.
     *
     * @param Application $application
     *
     * @return void
     */
    public function setApplication(Application $application);
}
