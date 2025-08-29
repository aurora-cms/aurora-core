<?php

namespace Aurora\Tests\Infrastructure\Persistence\ContentRepository;

use Aurora\Domain\ContentRepository\Exception\NodeTypeNotFound;
use Aurora\Domain\ContentRepository\Type\NodeType;
use PHPUnit\Framework\TestCase;

class InMemoryNodeTypeRepository extends TestCase
{
    public function testInitializeWithoutSeed(): void
    {
        $repository = new \Aurora\Infrastructure\Persistence\ContentRepository\InMemoryNodeTypeRepository();

        $this->assertEmpty($repository->all());
    }

    public function testInitializeWithSeed(): void
    {
        $typeA = new NodeType('typeA', []);
        $typeB = new NodeType('typeB', []);
        $seed = [$typeA, $typeB];

        $repository = new \Aurora\Infrastructure\Persistence\ContentRepository\InMemoryNodeTypeRepository($seed);

        $this->assertCount(2, $repository->all());
        $this->assertTrue($repository->has('typeA'));
        $this->assertTrue($repository->has('typeB'));
    }

    public function testRegisterAndRetrieveNodeType(): void
    {
        $repository = new \Aurora\Infrastructure\Persistence\ContentRepository\InMemoryNodeTypeRepository();
        $typeC = new NodeType('typeC', []);

        $repository->register($typeC);

        $this->assertTrue($repository->has('typeC'));
        $this->assertSame($typeC, $repository->get('typeC'));
    }

    public function testGetNonExistentNodeTypeThrowsException(): void
    {
        $this->expectException(NodeTypeNotFound::class);

        $repository = new \Aurora\Infrastructure\Persistence\ContentRepository\InMemoryNodeTypeRepository();
        $repository->get('nonExistentType');
    }

    public function testHasNonExistentNodeTypeReturnsFalse(): void
    {
        $repository = new \Aurora\Infrastructure\Persistence\ContentRepository\InMemoryNodeTypeRepository();
        $this->assertFalse($repository->has('nonExistentType'));
    }

    public function testAllReturnsAllRegisteredNodeTypes(): void
    {
        $typeD = new NodeType('typeD', []);
        $typeE = new NodeType('typeE', []);
        $repository = new \Aurora\Infrastructure\Persistence\ContentRepository\InMemoryNodeTypeRepository([$typeD, $typeE]);

        $allTypes = $repository->all();

        $this->assertCount(2, $allTypes);
        $this->assertContains($typeD, $allTypes);
        $this->assertContains($typeE, $allTypes);
    }
}
