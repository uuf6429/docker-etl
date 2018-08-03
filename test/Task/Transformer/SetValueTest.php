<?php

namespace uuf6429\DockerEtl\Task\Transformer;

use PHPUnit\Framework\TestCase;
use uuf6429\DockerEtl\Container\BindStorage;
use uuf6429\DockerEtl\Container\Container;
use uuf6429\DockerEtl\Container\Dictionary;
use uuf6429\DockerEtl\Container\StorageCollection;

class SetValueTest extends TestCase
{
    /**
     * @param string $optionValue
     * @param mixed $expectedState
     * @param null|string $expectedExceptionMessage
     *
     * @dataProvider setValueDataProvider
     */
    public function testSetValue($optionValue, $expectedState, $expectedExceptionMessage)
    {
        $sut = new SetValue();
        $container = new Container();
        $container->volumes->add(new BindStorage());

        if ($expectedExceptionMessage) {
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        $sut->execute($container, $optionValue);
        $this->assertEquals($expectedState, (object)get_object_vars($container));
    }

    public function setValueDataProvider()
    {
        $storage = new StorageCollection();
        $storage->add(new BindStorage());

        return [
            'set container image' => [
                '$optionValue' => 'image="myimage:1234"',
                '$expectedState' => (object)[
                    'name' => '',
                    'image' => 'myimage:1234',
                    'labels' => new Dictionary(),
                    'environment' => new Dictionary(),
                    'entrypoint' => '',
                    'cmd' => [],
                    'volumes' => $storage,
                    'workingDir' => '',
                ],
                '$expectedExceptionMessage' => null,
            ],
            'set nonexistent property' => [
                '$optionValue' => 'foo="bar"',
                '$expectedState' => null,
                '$expectedExceptionMessage' => 'Cannot set foo; property foo does not seem to exist.',
            ],
            'set nonexistent key' => [
                '$optionValue' => 'volumes[0].dummykey="dummyvalue"',
                '$expectedState' => null,
                '$expectedExceptionMessage' => 'Cannot set volumes[0].dummykey; property dummykey does not seem to exist.',
            ],
            'set nonexistent property in a non-object' => [
                '$optionValue' => 'name.foo="bar"',
                '$expectedState' => null,
                '$expectedExceptionMessage' => 'Cannot set name.foo; name is not an object.',
            ],
        ];
    }
}
