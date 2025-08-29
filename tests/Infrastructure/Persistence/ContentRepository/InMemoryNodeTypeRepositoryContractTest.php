<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Tests\Infrastructure\Persistence\ContentRepository;

use Aurora\Infrastructure\Persistence\ContentRepository\InMemoryNodeTypeRepository;
use Aurora\Tests\Application\ContentRepository\Port\NodeTypeRepositoryContractTest;

final class InMemoryNodeTypeRepositoryContractTest extends NodeTypeRepositoryContractTest
{
    protected function createRepository(): \Aurora\Application\ContentRepository\Port\NodeTypeRepository
    {
        return new InMemoryNodeTypeRepository();
    }
}

