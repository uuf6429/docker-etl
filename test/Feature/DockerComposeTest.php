<?php

namespace uuf6429\DockerEtl\Test\Feature;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class DockerComposeTest extends TestCase
{
    use ReflectXDebugConfigTrait;

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

    /**
     * @param string $serviceName
     * @param string[] $additionalTasks
     * @param string[] $expectedStdOut
     * @param string[] $expectedStdErr
     *
     * @dataProvider modifyContainerDataProvider
     */
    public function testModifyingContainer($serviceName, array $additionalTasks, array $expectedStdOut, array $expectedStdErr)
    {
        $testProcess = new Process(
            array_merge(
                [
                    'php',
                ],
                $this->buildPhpArgs(),
                [
                    'docker-etl',
                    "--extract-from-docker-compose={$this->testFile}:{$serviceName}",
                ],
                $additionalTasks,
                [
                    '--load-into-docker-cmd=>>php://stdout',
                    '-vvv',
                ]
            ),
            dirname(TEST_ROOT)
        );
        $testProcess
            ->setTimeout(null)
            ->mustRun();

        $this->assertEquals(
            [
                'stdout' => $expectedStdOut,
                'stderr' => $expectedStdErr,
            ],
            [
                'stdout' => explode(PHP_EOL, $testProcess->getOutput()),
                'stderr' => explode(PHP_EOL, str_replace($this->testFile, 'docker-compose.yml', $testProcess->getErrorOutput()))
            ]
        );
    }

    /**
     * @return array
     */
    public function modifyContainerDataProvider()
    {
        return [
            'modify simple service (redis)' => [
                '$serviceName' => 'redis',
                '$additionalTasks' => ['--set=image="redis:alpine"'],
                '$expectedStdOut' =>  [
                    'docker run "redis:alpine"',
                    ''
                ],
                '$expectedStdErr' => [
                    '[debug] Applying task --extract-from-docker-compose=docker-compose.yml:redis ...',
                    '[debug] Applying task --set=image="redis:alpine" ...',
                    '[debug] Applying task --load-into-docker-cmd=>>php://stdout ...',
                    '',
                ],
            ],
            'modify built web service (web)' => [
                '$serviceName' => 'web',
                '$additionalTasks' => [
                    '--set=image="pypy:3"',
                    '--set=cmd[0]="pypy3"'
                ],
                '$expectedStdOut' =>  [
                    'docker run "--env" "DATADOG_HOST=datadog" "pypy:3" pypy3 "app.py"',
                    ''
                ],
                '$expectedStdErr' => [
                    '[debug] Applying task --extract-from-docker-compose=docker-compose.yml:web ...',
                    '[warning] The service builds an image and therefore the image tag cannot be determined.',
                    '[warning] Some configuration was not used by uuf6429\DockerEtl\Task\Extractor\DockerCompose: service.ports, service.volumes, service.links',
                    '[debug] Applying task --set=image="pypy:3" ...',
                    '[debug] Applying task --set=cmd[0]="pypy3" ...',
                    '[debug] Applying task --load-into-docker-cmd=>>php://stdout ...',
                    '',
                ],
            ],
        ];
    }
}
