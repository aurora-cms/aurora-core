<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Tests\Infrastructure\Persistence\ContentRepository;

use Aurora\Infrastructure\Persistence\ContentRepository\Doctrine\DoctrineNodeTypeRepository;
use Aurora\Infrastructure\Persistence\ContentRepository\Doctrine\Entity\NodeTypeRecord;
use Aurora\Tests\Application\ContentRepository\Port\NodeTypeRepositoryContractTest;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineNodeTypeRepositoryTest extends NodeTypeRepositoryContractTest
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
            $this->em->getClassMetadata(NodeTypeRecord::class),
        ];
        $tool->updateSchema($classes);

        // cleanup table
        $conn = $this->em->getConnection();
        $platform = $conn->getDatabasePlatform();
        $table = $this->em->getClassMetadata(NodeTypeRecord::class)->getTableName();
        $conn->executeStatement(sprintf('DELETE FROM %s', $platform->quoteSingleIdentifier($table)));
    }

    protected function createRepository(): \Aurora\Application\ContentRepository\Port\NodeTypeRepository
    {
        return new DoctrineNodeTypeRepository($this->em);
    }
}
