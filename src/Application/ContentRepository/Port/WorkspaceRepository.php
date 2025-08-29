<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Application\ContentRepository\Port;

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
     * @param Workspace $workspace the workspace to save
     */
    public function save(Workspace $workspace): void;

    /**
     * Retrieves a Workspace by its ID and dimension set.
     *
     * @param WorkspaceId  $id           the workspace identifier
     * @param DimensionSet $dimensionSet the dimension set context
     *
     * @return Workspace the requested workspace
     */
    public function get(WorkspaceId $id, DimensionSet $dimensionSet): Workspace;

    /**
     * Checks if a Workspace exists for the given ID and dimension set.
     *
     * @param WorkspaceId  $id           the workspace identifier
     * @param DimensionSet $dimensionSet the dimension set context
     *
     * @return bool true if the workspace exists, false otherwise
     */
    public function exists(WorkspaceId $id, DimensionSet $dimensionSet): bool;
}
