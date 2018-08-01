<?php

namespace uuf6429\DockerEtl;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class CmdIntegrationTest extends TestCase
{
    private $testContainers = [];

    protected function tearDown()
    {
        (new Process(
            'docker rm -f -v ' . implode(' ', $this->testContainers)
        ))->mustRun();

        parent::tearDown();
    }

    public function testCloningContainer()
    {
        $originalContainer = uniqid('phpunit-test-', true);
        $this->testContainers[] = $originalContainer;
        (new Process("docker run --name $originalContainer hello-world"))->mustRun();

        $testProcess = new Process(
            [
                'php',
                'docker-etl',
                "--extract-from-docker-cmd=$originalContainer",
                '--set=image="php:7-alpine"',
                '--set=cmd=["php","-v"]',
                '--load-into-docker-cmd=>>php://stdout',
                '-vvv',
            ],
            dirname(__DIR__)
        );
        $testProcess->mustRun();

        $this->assertEquals(
            [
                'stdout' => [
                    'docker run "--name" "' . $originalContainer . '" "php:7-alpine" php "-v"',
                    ''
                ],
                'stderr' => [
                    "[debug] Applying task --extract-from-docker-cmd={$originalContainer} ...",
                    '[debug] Applying task --set=image="php:7-alpine" ...',
                    '[debug] Applying task --set=cmd=["php","-v"] ...',
                    '[debug] Applying task --load-into-docker-cmd=>>php://stdout ...',
                    '',
                ],
            ],
            [
                'stdout' => explode(PHP_EOL, $testProcess->getOutput()),
                'stderr' => explode(PHP_EOL, $testProcess->getErrorOutput())
            ]
        );
    }
}
