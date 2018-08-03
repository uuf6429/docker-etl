<?php

namespace uuf6429\DockerEtl;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ComposeIntegrationTest extends TestCase
{
    private $testFile;

    protected function setUp()
    {
        $this->testFile = tempnam(sys_get_temp_dir(), 'cps-');

        file_put_contents(
            $this->testFile,
            /**  */
            <<<'YAML'
version: "2"
services:
  web:
    build: web
    command: python app.py
    ports:
     - "5000:5000"
    volumes:
     - ./web:/code
    links:
     - redis
    environment:
     - DATADOG_HOST=datadog
  redis:
    image: redis
  # agent section
  datadog:
    build: datadog
    links:
     - redis
     - web
    environment:
     - API_KEY=__your_datadog_api_key_here__
    volumes:
     - /var/run/docker.sock:/var/run/docker.sock
     - /proc/mounts:/host/proc/mounts:ro
     - /sys/fs/cgroup:/host/sys/fs/cgroup:ro
YAML
        );

        parent::setUp();
    }

    protected function tearDown()
    {
        unlink($this->testFile);

        parent::tearDown();
    }

    public function testModifyingRedisContainer()
    {
        $testProcess = new Process(
            [
                'php',
                'docker-etl',
                "--extract-from-docker-compose={$this->testFile}:redis",
                '--set=image="redis:alpine"',
                '--load-into-docker-cmd=>>php://stdout',
                '-vvv',
            ],
            dirname(__DIR__)
        );
        $testProcess->mustRun();

        $this->assertEquals(
            [
                'stdout' => [
                    'docker run "redis:alpine"',
                    ''
                ],
                'stderr' => [
                    "[debug] Applying task --extract-from-docker-compose={$this->testFile}:redis ...",
                    '[debug] Applying task --set=image="redis:alpine" ...',
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
