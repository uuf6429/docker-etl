<?php

namespace uuf6429\DockerEtl\Console;

use Symfony\Component\Console\Output\OutputInterface;

interface OutputAwareInterface
{
    public function setOutput(OutputInterface $output);
}
