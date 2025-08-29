<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Application\ContentRepository\UseCase;

use Aurora\Application\ContentRepository\Dto\GetNodeByPathResponse;
use Aurora\Application\ContentRepository\Port\WorkspaceRepository;
use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodePath;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;

/**
 * Handler for retrieving a node by its path within a workspace and dimension set.
 *
 * @readonly
 */
final readonly class GetNodeByPathHandler
{
    /**
     * Constructor.
     *
     * @param WorkspaceRepository $repo the repository to access workspaces
     */
    public function __construct(
        private WorkspaceRepository $repo,
    ) {
    }

    /**
     * Invokes the handler to get a node by its path.
     *
     * @param string                $workspaceId the workspace identifier
     * @param array<string, string> $dimensions  the dimension set for the query
     * @param string                $path        the path to the node
     *
     * @return GetNodeByPathResponse the response containing node details
     */
    public function __invoke(string $workspaceId, array $dimensions, string $path): GetNodeByPathResponse
    {
        $ws = $this->repo->get(
            WorkspaceId::fromString($workspaceId),
            new DimensionSet($dimensions),
        );
        $node = $ws->getByPath(NodePath::fromString($path));

        return new GetNodeByPathResponse(
            nodeId: (string) $node->id,
            path: (string) $node->path,
            nodeType: $node->type->name,
            properties: $node->properties,
        );
    }
}
