<?php

namespace uuf6429\DockerEtl\Task\Transformer;

use PHPUnit\Framework\TestCase;
use uuf6429\DockerEtl\Container\State;
use uuf6429\DockerEtl\Container\Dictionary;
use uuf6429\DockerEtl\Container\VolumeCollection;

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
        $container = new State();

        if($expectedExceptionMessage){
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        $sut->execute($container, $optionValue);
        $this->assertEquals($expectedState, (object)get_object_vars($container));
    }

    public function setValueDataProvider()
    {
        return [
            'set container image' => [
                '$optionValue' => 'image=myimage:1234',
                '$expectedState' => (object)[
                    'name' => null,
                    'image' => 'myimage:1234',
                    'labels' => new Dictionary(),
                    'environment' => new Dictionary(),
                    'entrypoint' => null,
                    'cmd' => null,
                    'volumes' => new VolumeCollection(),
                ],
                '$expectedExceptionMessage' => null,
            ],
            'set nonexistent property' => [
                '$optionValue' => 'foo=bar',
                '$expectedState' => null,
                '$expectedExceptionMessage' => 'Cannot set foo; property foo does not seem to exist.',
            ],
            'set nonexistent key' => [
                '$optionValue' => 'volumes[0].dummykey=dummyvalue',
                '$expectedState' => null,
                '$expectedExceptionMessage' => 'Cannot set volumes[0].dummykey; property dummykey does not seem to exist.',
            ],
            'set nonexistent property in a non-object' => [
                '$optionValue' => 'name.foo=bar',
                '$expectedState' => null,
                '$expectedExceptionMessage' => 'Cannot set name.foo; name is not an object.',
            ],
        ];
    }
}
