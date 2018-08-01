<?php

namespace uuf6429\DockerEtl\Console;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Basic Implementation of OutputAwareInterface.
 */
trait OutputAwareTrait
{
    /**
     * The output instance.
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * Sets the output.
     *
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }
}
