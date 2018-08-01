<?php

namespace uuf6429\DockerEtl\Console;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Describes an output-aware instance.
 */
interface OutputAwareInterface
{
    /**
     * Sets an output instance on the object.
     *
     * @param OutputInterface $output
     *
     * @return void
     */
    public function setOutput(OutputInterface $output);
}
