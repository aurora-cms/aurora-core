<?php

declare(strict_types=1);

namespace Aurora\Tests\Domain\ContentRepository;

use Aurora\Domain\ContentRepository\Value\DimensionSet;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class DimensionSetValidationTest extends TestCase
{
    public function testEmptyNameOrValueIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new DimensionSet(['' => 'x']);
    }

    public function testEmptyValueIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new DimensionSet(['x' => '']);
    }

    public function testNameMustStartWithLetter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new DimensionSet(['1locale' => 'en']);
    }

    public function testInvalidCharactersAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new DimensionSet(['loc!' => 'en']);
    }
}

