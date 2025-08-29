<?php

declare(strict_types=1);

namespace Aurora\Tests\Domain\ContentRepository;

use Aurora\Domain\ContentRepository\Exception\NodePathInvalid;
use Aurora\Domain\ContentRepository\Value\NodePath;
use PHPUnit\Framework\TestCase;

final class NodePathAppendTest extends TestCase
{
    public function testAppendNormalizesTrimAndLowercase(): void
    {
        $p = NodePath::root()->append(' A ');
        $this->assertSame('/a', (string) $p);
    }

    public function testAppendInvalidSegmentThrows(): void
    {
        $this->expectException(NodePathInvalid::class);
        NodePath::root()->append('Invalid*Segment');
    }

    public function testEquals(): void
    {
        $a = NodePath::fromString('/x/y');
        $b = NodePath::fromString('/x/y');
        $c = NodePath::fromString('/x/z');
        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }
}

