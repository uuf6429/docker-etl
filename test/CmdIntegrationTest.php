<?php

namespace uuf6429\DockerEtl;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class CmdIntegrationTest extends TestCase
{
    use ReflectXDebugConfigTrait;

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
        (new Process("docker run --name $originalContainer hello-world"))
            ->setTimeout(null)
            ->mustRun();

        $testProcess = new Process(
            array_merge(
                [
                    'php',
                ],
                $this->buildPhpArgs(),
                [
                    'docker-etl',
                    "--extract-from-docker-cmd=$originalContainer",
                    '--set=image="php:7-alpine"',
                    '--set=cmd=["php","-v"]',
                    '--load-into-docker-cmd=>>php://stdout',
                    '-vvv',
                ]
            ),
            dirname(__DIR__)
        );
        $testProcess
            ->setTimeout(null)
            ->mustRun();

        $this->assertEquals(
            [
                'stdout' => [
                    'docker run "--env" "PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin" "--name" "' . $originalContainer . '" "php:7-alpine" php "-v"',
                    ''
                ],
                'stderr' => [
                    "[debug] Applying task --extract-from-docker-cmd={$originalContainer} ...",
                    '[warning] Some configuration was not used by uuf6429\DockerEtl\Task\Extractor\DockerCmd: Path, Args, Image, ResolvConfPath, HostnamePath, HostsPath, LogPath, Driver, Platform, MountLabel, ProcessLabel, AppArmorProfile, ExecIDs, HostConfig.Binds, HostConfig.ContainerIDFile, HostConfig.LogConfig.Type, HostConfig.NetworkMode, HostConfig.RestartPolicy.Name, HostConfig.RestartPolicy.MaximumRetryCount, HostConfig.AutoRemove, HostConfig.VolumeDriver, HostConfig.VolumesFrom, HostConfig.CapAdd, HostConfig.CapDrop, HostConfig.Dns, HostConfig.DnsOptions, HostConfig.DnsSearch, HostConfig.ExtraHosts, HostConfig.GroupAdd, HostConfig.IpcMode, HostConfig.Cgroup, HostConfig.Links, HostConfig.OomScoreAdj, HostConfig.PidMode, HostConfig.Privileged, HostConfig.PublishAllPorts, HostConfig.ReadonlyRootfs, HostConfig.SecurityOpt, HostConfig.UTSMode, HostConfig.UsernsMode, HostConfig.ShmSize, HostConfig.Runtime, HostConfig.ConsoleSize, HostConfig.Isolation, HostConfig.CpuShares, HostConfig.Memory, HostConfig.NanoCpus, HostConfig.CgroupParent, HostConfig.BlkioWeight, HostConfig.BlkioWeightDevice, HostConfig.BlkioDeviceReadBps, HostConfig.BlkioDeviceWriteBps, HostConfig.BlkioDeviceReadIOps, HostConfig.BlkioDeviceWriteIOps, HostConfig.CpuPeriod, HostConfig.CpuQuota, HostConfig.CpuRealtimePeriod, HostConfig.CpuRealtimeRuntime, HostConfig.CpusetCpus, HostConfig.CpusetMems, HostConfig.Devices, HostConfig.DeviceCgroupRules, HostConfig.DiskQuota, HostConfig.KernelMemory, HostConfig.MemoryReservation, HostConfig.MemorySwap, HostConfig.MemorySwappiness, HostConfig.OomKillDisable, HostConfig.PidsLimit, HostConfig.Ulimits, HostConfig.CpuCount, HostConfig.CpuPercent, HostConfig.IOMaximumIOps, HostConfig.IOMaximumBandwidth, GraphDriver.Data, GraphDriver.Name, Config.Hostname, Config.Domainname, Config.User, Config.AttachStdin, Config.AttachStdout, Config.AttachStderr, Config.Tty, Config.OpenStdin, Config.StdinOnce, Config.ArgsEscaped, Config.OnBuild, NetworkSettings.Bridge, NetworkSettings.SandboxID, NetworkSettings.HairpinMode, NetworkSettings.LinkLocalIPv6Address, NetworkSettings.LinkLocalIPv6PrefixLen, NetworkSettings.SandboxKey, NetworkSettings.SecondaryIPAddresses, NetworkSettings.SecondaryIPv6Addresses, NetworkSettings.EndpointID, NetworkSettings.Gateway, NetworkSettings.GlobalIPv6Address, NetworkSettings.GlobalIPv6PrefixLen, NetworkSettings.IPAddress, NetworkSettings.IPPrefixLen, NetworkSettings.IPv6Gateway, NetworkSettings.MacAddress, NetworkSettings.Networks.bridge.IPAMConfig, NetworkSettings.Networks.bridge.Links, NetworkSettings.Networks.bridge.Aliases, NetworkSettings.Networks.bridge.NetworkID, NetworkSettings.Networks.bridge.EndpointID, NetworkSettings.Networks.bridge.Gateway, NetworkSettings.Networks.bridge.IPAddress, NetworkSettings.Networks.bridge.IPPrefixLen, NetworkSettings.Networks.bridge.IPv6Gateway, NetworkSettings.Networks.bridge.GlobalIPv6Address, NetworkSettings.Networks.bridge.GlobalIPv6PrefixLen, NetworkSettings.Networks.bridge.MacAddress, NetworkSettings.Networks.bridge.DriverOpts',
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
