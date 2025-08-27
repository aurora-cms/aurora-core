<?php

namespace Aurora\Application\ContentRepository;

use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;
use Aurora\Domain\ContentRepository\Workspace;

/**
 * Interface for managing Workspace entities in the content repository.
 */
interface WorkspaceRepository
{
    /**
     * Persists a Workspace entity.
     *
     * @param Workspace $workspace The workspace to save.
     */
    public function save(Workspace $workspace): void;

    /**
     * Retrieves a Workspace by its ID and dimension set.
     *
     * @param WorkspaceId $id The workspace identifier.
     * @param DimensionSet $dimensionSet The dimension set context.
     * @return Workspace The requested workspace.
     */
    public function get(WorkspaceId $id, DimensionSet $dimensionSet): Workspace;

    /**
     * Checks if a Workspace exists for the given ID and dimension set.
     *
     * @param WorkspaceId $id The workspace identifier.
     * @param DimensionSet $dimensionSet The dimension set context.
     * @return bool True if the workspace exists, false otherwise.
     */
    public function exists(WorkspaceId $id, DimensionSet $dimensionSet): bool;
}
