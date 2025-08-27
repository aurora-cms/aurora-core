<?php

namespace Aurora\Tests\Domain\ContentRepository;

use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodeId;
use PHPUnit\Framework\TestCase;

class ValueTest extends TestCase
{

    public function testCreateEmptyDimensionSet(): void
    {
        $dimensionSet = DimensionSet::empty();
        $this->assertEmpty($dimensionSet->all());
    }

    public function testDimensionSetToString(): void
    {
        $dimensionSet = new DimensionSet(['locale' => 'en_US', 'device' => 'mobile']);
        $this->assertEquals('{device=mobile;locale=en_US}', (string)$dimensionSet);

        $dimensionSet = DimensionSet::empty();
        $this->assertEquals('{}', (string)$dimensionSet);
    }

    public function testDimensionSetNormalization(): void
    {
        $dimensionSet = new DimensionSet([' Locale ' => 'en_US', 'DEVICE' => 'mobile']);
        $this->assertEquals(['device' => 'mobile', 'locale' => 'en_US'], $dimensionSet->all());
    }

    public function testNodeIdFromString(): void
    {
        $nid = NodeId::fromString('a1b2c3d4e5f6g7h8i9j0');
        $this->assertEquals('a1b2c3d4e5f6g7h8i9j0', (string)$nid);

        $nid = NodeId::fromString('345bf989-2774-4ac3-b117-c7d0dec40675');
        $this->assertEquals('345bf989-2774-4ac3-b117-c7d0dec40675', (string)$nid);
    }

    public function testNodeIdToStringIncorrect(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new NodeId('invalid id!');
        $this->expectException(\InvalidArgumentException::class);
        new NodeId('a');
    }

    public function testNodeIdToStringEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new NodeId(' ');
    }
}
