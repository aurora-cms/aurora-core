<?php

declare(strict_types=1);

namespace Aurora\Tests\Domain\ContentRepository;

use Aurora\Domain\ContentRepository\Type\PropertyDefinition;
use Aurora\Domain\ContentRepository\Type\PropertyType;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PropertyDefinitionTest extends TestCase
{
    public function testDefaultNullableIsFalse(): void
    {
        $pd = new PropertyDefinition('p', PropertyType::STRING);
        $this->expectException(InvalidArgumentException::class);
        $pd->validate(null);
    }

    public function testNameValidationAnchors(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Property name format invalid: bad!');
        new PropertyDefinition('bad!', PropertyType::STRING);
    }
}
