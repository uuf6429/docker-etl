<?php

namespace uuf6429\DockerEtl\PathMarker;

use PHPUnit\Framework\TestCase;

class PathMarkerTest extends TestCase
{
    /**
     * @param mixed $sourceData
     * @param array $expectedPaths
     * @param null|string $expectedExceptionMessage
     *
     * @dataProvider mappingPathsDataProvider
     */
    public function testMappingPaths($sourceData, $expectedPaths, $expectedExceptionMessage)
    {
        $sut = new PathMarker();

        if ($expectedExceptionMessage) {
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        $sut->mapToPaths($sourceData);

        $this->assertEquals(
            $expectedPaths,
            $sut->getUnmarkedPaths()
        );
    }

    public function mappingPathsDataProvider()
    {
        return [
            'empty array' => [
                '$sourceData' => [],
                '$expectedPaths' => [],
                '$expectedExceptionMessage' => null,
            ],
            'empty object' => [
                '$sourceData' => (object)[],
                '$expectedPaths' => [],
                '$expectedExceptionMessage' => null,
            ],
            'simple hash map' => [
                '$sourceData' => [
                    'foo' => 100,
                    'bar' => 200,
                ],
                '$expectedPaths' => [],
                '$expectedExceptionMessage' => null,
            ],
            'simple object' => [
                '$sourceData' => (object)[
                    'foo' => 100,
                    'bar' => 200,
                ],
                '$expectedPaths' => [
                    'foo',
                    'bar',
                ],
                '$expectedExceptionMessage' => null,
            ],
            'simple array' => [
                '$sourceData' => ['foo', 'bar'],
                '$expectedPaths' => [],
                '$expectedExceptionMessage' => null,
            ],
            'object containing some stuff' => [
                '$sourceData' => (object)[
                    'foo' => 100,
                    'bar' => (object)[
                        'foo' => 1,
                        'bar' => 2,
                        'baz' => 3,
                    ],
                    'baz' => [100, 200]
                ],
                '$expectedPaths' => [
                    'foo',
                    'bar.foo',
                    'bar.bar',
                    'bar.baz',
                    'baz',
                ],
                '$expectedExceptionMessage' => null,
            ],
            'object with non-public properties' => [
                '$sourceData' => new PathMarkerTestDataClass(),
                '$expectedPaths' => ['foo'],
                '$expectedExceptionMessage' => null,
            ],
            'invalid value' => [
                '$sourceData' => 1234,
                '$expectedPaths' => [],
                '$expectedExceptionMessage' => 'Value to map must be an object or array, got integer instead.',
            ],
        ];
    }

    public function testManagingMarkers()
    {
        $sut = new PathMarker();

        $sut->addPaths(['foo', 'bar']);
        $this->assertTrue($sut->hasUnmarkedPaths());
        $this->assertEquals(['foo', 'bar'], $sut->getUnmarkedPaths());

        $sut->markPath('foo');
        $this->assertEquals(['bar'], $sut->getUnmarkedPaths());

        $sut->markPath('bar');
        $this->assertFalse($sut->hasUnmarkedPaths());

        $sut->unmarkAllPaths();
        $this->assertEquals(['foo', 'bar'], $sut->getUnmarkedPaths());
        $this->assertTrue($sut->pathExist('bar'));

        $sut->removePaths(['bar']);
        $this->assertFalse($sut->pathExist('bar'));
        $this->assertEquals(['foo'], $sut->getUnmarkedPaths());

        $sut->removeAllPaths();
        $this->assertEquals([], $sut->getUnmarkedPaths());
    }

    public function testMarkingNonExistentPath()
    {
        $sut = new PathMarker();
        $sut->addPaths(['foo']);

        $this->expectExceptionMessage('Cannot mark path "bar" since it does not exist.');
        $sut->markPath('bar');
    }

    public function testGettingUniqueUnmarkedPaths()
    {
        $sut = new PathMarker();
        $sut->addPaths(['foo[bar].baz', 'foo[bzz].baz', 'bar.aa[0]', 'baz[0]', 'baz[1]', 'bar.aa[2]']);
        $sut->markPath('baz[0]');
        $sut->markPath('baz[1]');

        $this->assertEquals(
            ['foo[*].baz', 'bar.aa[*]'],
            $sut->getUniqueUnmarkedPaths()
        );
    }
}
