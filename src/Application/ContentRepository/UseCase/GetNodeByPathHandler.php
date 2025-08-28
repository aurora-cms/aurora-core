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

final readonly class GetNodeByPathHandler
{
    public function __construct(
        private WorkspaceRepository $repo,
    ) {
    }

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
