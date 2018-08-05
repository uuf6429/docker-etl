<?php

namespace uuf6429\DockerEtl\Test\Feature;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ExtensionTest extends TestCase
{
    use ReflectXDebugConfigTrait;

    public function testExtensionMentionedInHelp()
    {
        $testProcess = new Process(
            array_merge(
                [
                    'php',
                ],
                $this->buildPhpArgs(),
                [
                    'docker-etl',
                    'help',
                    'run',
                    '--include='.__DIR__.'/ExtensionTestInclude.php',
                ]
            ),
            dirname(TEST_ROOT)
        );
        $testProcess
            ->setTimeout(null)
            ->mustRun();

        $this->assertContains('--greet[=GREET]', $testProcess->getOutput());
    }

    public function testExtensionIsTriggered()
    {
        $testProcess = new Process(
            array_merge(
                [
                    'php',
                ],
                $this->buildPhpArgs(),
                [
                    'docker-etl',
                    '--include='.__DIR__.'/ExtensionTestInclude.php',
                    '--greet=Good Afternoon',
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
                'stdout' => [
                    'Good Afternoon World!',
                    '',
                ],
                'stderr' => [
                    '[debug] Applying task --greet=Good Afternoon ...',
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
