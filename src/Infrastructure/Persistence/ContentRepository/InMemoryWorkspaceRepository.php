<?php

namespace Aurora\Infrastructure\Persistence\ContentRepository;

use Aurora\Application\ContentRepository\WorkspaceRepository;
use Aurora\Domain\ContentRepository\Exception\WorkspaceNotFound;
use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;
use Aurora\Domain\ContentRepository\Workspace;

class InMemoryWorkspaceRepository implements WorkspaceRepository
{
    /**
     * In-memory store for workspaces.
     *
     * @var array<string, Workspace>
     */
    private array $store = [];

    private function key(WorkspaceId $id, DimensionSet $dimensionSet): string
    {
        return $id . '|' . $dimensionSet;
    }

    /**
     * @inheritDoc
     */
    public function save(Workspace $workspace): void
    {
        $this->store[$this->key($workspace->id, $workspace->dimensionSet)] = $workspace;
    }

    /**
     * @inheritDoc
     */
    public function get(WorkspaceId $id, DimensionSet $dimensionSet): Workspace
    {
        $key = $this->key($id, $dimensionSet);
        if (!isset($this->store[$key])) {
            throw new WorkspaceNotFound("Workspace not found for ID {$id} and dimensions {$dimensionSet}");
        }
        return $this->store[$key];
    }

    /**
     * @inheritDoc
     */
    public function exists(WorkspaceId $id, DimensionSet $dimensionSet): bool
    {
        return isset($this->store[$this->key($id, $dimensionSet)]);
    }
}
