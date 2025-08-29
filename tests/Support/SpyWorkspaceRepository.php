<?php

declare(strict_types=1);

namespace Aurora\Tests\Support;

use Aurora\Application\ContentRepository\Port\WorkspaceRepository;
use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;
use Aurora\Domain\ContentRepository\Workspace;
use Aurora\Infrastructure\Persistence\ContentRepository\InMemoryWorkspaceRepository;

final class SpyWorkspaceRepository implements WorkspaceRepository
{
    private InMemoryWorkspaceRepository $inner;
    public int $saveCount = 0;

    public function __construct()
    {
        $this->inner = new InMemoryWorkspaceRepository();
    }

    public function save(Workspace $workspace): void
    {
        $this->saveCount++;
        $this->inner->save($workspace);
    }

    public function get(WorkspaceId $id, DimensionSet $dimensionSet): Workspace
    {
        return $this->inner->get($id, $dimensionSet);
    }

    public function exists(WorkspaceId $id, DimensionSet $dimensionSet): bool
    {
        return $this->inner->exists($id, $dimensionSet);
    }
}

