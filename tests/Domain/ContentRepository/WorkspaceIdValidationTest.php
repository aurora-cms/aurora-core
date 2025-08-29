<?php

declare(strict_types=1);

namespace Aurora\Tests\Domain\ContentRepository;

use Aurora\Domain\ContentRepository\Value\WorkspaceId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class WorkspaceIdValidationTest extends TestCase
{
    public function testRejectsLeadingSpaceEvenIfTailValid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('WorkspaceId format invalid');
        new WorkspaceId(' dr');
    }

    public function testEmptyStringOrSpacesAreInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('WorkspaceId cannot be empty.');
        new WorkspaceId('   ');
    }

    public function testRejectsTooShort(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('WorkspaceId format invalid');
        new WorkspaceId('a');
    }

    public function testRejectsInvalidCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('WorkspaceId format invalid');
        new WorkspaceId('dr!');
    }
}
