<?php

declare(strict_types=1);

namespace Aurora\Tests\Domain\ContentRepository;

use Aurora\Domain\ContentRepository\Exception\NodePathInvalid;
use Aurora\Domain\ContentRepository\Value\NodePath;
use PHPUnit\Framework\TestCase;

final class NodePathValidationTest extends TestCase
{
    public function testTrimsInputAndValidatesStartsWithSlash(): void
    {
        $p = NodePath::fromString('  /a/b  ');
        $this->assertSame('/a/b', (string) $p);
    }

    public function testRejectsDotAndDotDotSegments(): void
    {
        $this->expectException(NodePathInvalid::class);
        NodePath::fromString('/a/./b');
    }

    public function testRejectsDotDotSegment(): void
    {
        $this->expectException(NodePathInvalid::class);
        NodePath::fromString('/a/../b');
    }

    public function testRemovesTrailingSlash(): void
    {
        $p = NodePath::fromString('/a/b/');
        $this->assertSame('/a/b', (string) $p);
    }
}

