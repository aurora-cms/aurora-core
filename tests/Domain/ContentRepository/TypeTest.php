<?php

namespace Aurora\Tests\Domain\ContentRepository;

use Aurora\Domain\ContentRepository\Exception\UndefinedProperty;
use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Domain\ContentRepository\Type\PropertyDefinition;
use Aurora\Domain\ContentRepository\Type\PropertyType;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    public function testNodeTypeHasProperty(): void
    {
        $nt = new NodeType('root', [new PropertyDefinition('id', PropertyType::STRING, false)]);
        $this->assertTrue($nt->has('id'));
        $this->assertFalse($nt->has('title'));
    }

    public function testNodeTypeDefinition(): void
    {
        $nt = new NodeType('root', [new PropertyDefinition('id', PropertyType::STRING, false)]);
        $this->assertInstanceOf(PropertyDefinition::class, $nt->definition('id'));
        $this->expectException(UndefinedProperty::class);
        $nt->definition('title');
    }

    public function testNodeTypeToString(): void
    {
        $nt = new NodeType('root');
        $this->assertSame('root', (string) $nt);

        $nt = new NodeType('my_type', [new PropertyDefinition('id', PropertyType::STRING, false)]);
        $this->assertSame('my_type', (string) $nt);
    }


}
