<?php

namespace uuf6429\DockerEtl\Test\Feature;

use uuf6429\DockerEtl\Console\OutputAwareInterface;
use uuf6429\DockerEtl\Console\OutputAwareTrait;
use uuf6429\DockerEtl\Container\Container;
use uuf6429\DockerEtl\Task\Task;

class ExtensionTestInclude extends Task implements OutputAwareInterface
{
    use OutputAwareTrait;

    public function getTaskOptionName()
    {
        return '--greet';
    }

    public function getTaskOptionMode()
    {
        return self::VALUE_OPTIONAL;
    }

    public function getTaskOptionDescription()
    {
        return 'Writes greeting in console output. Default greeting is <info>"hello"</info>, but can be changed by setting the option value.';
    }

    public function execute(Container $container, $value)
    {
        $this->output->writeln(ucfirst($value ?: 'Hello') . ' World!');
    }
}

/** @var \uuf6429\DockerEtl\Console\Application $this */
$this->addTasks([new ExtensionTestInclude()]);
