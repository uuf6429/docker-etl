<?php

namespace uuf6429\DockerEtl\Console;

interface ApplicationAwareInterface
{
    public function setApplication(Application $application);
}
