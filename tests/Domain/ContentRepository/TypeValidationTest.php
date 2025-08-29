<?php

declare(strict_types=1);

namespace Aurora\Tests\Domain\ContentRepository;

use Aurora\Domain\ContentRepository\Exception\PropertyValidationFailed;
use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Domain\ContentRepository\Type\PropertyDefinition;
use Aurora\Domain\ContentRepository\Type\PropertyType;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class TypeValidationTest extends TestCase
{
    public function testNodeTypeNameValidationAnchors(): void
    {
        // must start with a letter
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Node type name format invalid: 1invalid');
        new NodeType('1invalid');
    }

    public function testNodeTypeNameInvalidCharNotAllowed(): void
    {
        // invalid trailing character that should be rejected when $ anchor is enforced
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Node type name format invalid: valid*');
        new NodeType('valid*');
    }

    public function testValidatePropertiesEnforcesTypeAndNullability(): void
    {
        $nt = new NodeType('doc', [new PropertyDefinition('title', PropertyType::STRING)]);

        // wrong type should throw with specific message
        try {
            $nt->validateProperties(['title' => 123]);
            $this->fail('Expected PropertyValidationFailed');
        } catch (PropertyValidationFailed $e) {
            $this->assertStringContainsString('Expected string', $e->getMessage());
        }
    }

    public function testValidatePropertiesNullNotAllowedByDefault(): void
    {
        $nt = new NodeType('doc', [new PropertyDefinition('title', PropertyType::STRING)]);

        // null not allowed by default
        $this->expectException(InvalidArgumentException::class);
        $nt->validateProperties(['title' => null]);
    }
}
