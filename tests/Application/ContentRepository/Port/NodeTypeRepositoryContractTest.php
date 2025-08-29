<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Tests\Application\ContentRepository\Port;

use Aurora\Application\ContentRepository\Port\NodeTypeRepository;
use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Domain\ContentRepository\Type\PropertyDefinition;
use Aurora\Domain\ContentRepository\Type\PropertyType;
use PHPUnit\Framework\TestCase;

abstract class NodeTypeRepositoryContractTest extends TestCase
{
    /**
     * Implementations must provide a fresh repository instance.
     */
    abstract protected function createRepository(): NodeTypeRepository;

    public function testRegisterAndFetch(): void
    {
        $repo = $this->createRepository();

        $type = new NodeType('article', [
            new PropertyDefinition('title', PropertyType::STRING, false, false),
            new PropertyDefinition('views', PropertyType::INT, false, false),
        ]);

        $repo->register($type);

        self::assertTrue($repo->has('article'));
        $loaded = $repo->get('article');
        self::assertSame('article', $loaded->name);
        self::assertTrue($loaded->has('title'));
        self::assertTrue($loaded->has('views'));

        $all = $repo->all();
        self::assertNotEmpty($all);
    }
}
