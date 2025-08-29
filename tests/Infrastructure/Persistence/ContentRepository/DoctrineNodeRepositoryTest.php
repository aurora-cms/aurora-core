<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Tests\Infrastructure\Persistence\ContentRepository;

use Aurora\Infrastructure\Persistence\ContentRepository\Doctrine\DoctrineNodeRepository;
use Aurora\Infrastructure\Persistence\ContentRepository\Doctrine\DoctrineNodeTypeRepository;
use Aurora\Infrastructure\Persistence\ContentRepository\Doctrine\Entity\NodeRecord;
use Aurora\Infrastructure\Persistence\ContentRepository\Doctrine\Entity\NodeTypeRecord;
use Aurora\Tests\Application\ContentRepository\Port\NodeRepositoryContractTest;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineNodeRepositoryTest extends NodeRepositoryContractTest
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        /** @var EntityManagerInterface $em */
        $em = require __DIR__.'/../../../object-manager.php';
        $this->em = $em;

        // Ensure schema exists for tests
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = [
            $this->em->getClassMetadata(NodeRecord::class),
            $this->em->getClassMetadata(NodeTypeRecord::class),
        ];
        $tool->updateSchema($classes, true);

        $conn = $this->em->getConnection();
        $platform = $conn->getDatabasePlatform();
        $nodesTable = $this->em->getClassMetadata(NodeRecord::class)->getTableName();
        $typesTable = $this->em->getClassMetadata(NodeTypeRecord::class)->getTableName();
        $conn->executeStatement(sprintf('DELETE FROM %s', $platform->quoteSingleIdentifier($nodesTable)));
        $conn->executeStatement(sprintf('DELETE FROM %s', $platform->quoteSingleIdentifier($typesTable)));
    }

    protected function createNodeRepository(\Aurora\Application\ContentRepository\Port\NodeTypeRepository $types): \Aurora\Application\ContentRepository\Port\NodeRepository
    {
        return new DoctrineNodeRepository($this->em, $types);
    }

    protected function createNodeTypeRepository(): \Aurora\Application\ContentRepository\Port\NodeTypeRepository
    {
        return new DoctrineNodeTypeRepository($this->em);
    }
}
