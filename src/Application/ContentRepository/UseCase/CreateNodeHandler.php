<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Application\ContentRepository\UseCase;

use Aurora\Application\ContentRepository\Dto\CreateNodeRequest;
use Aurora\Application\ContentRepository\Dto\CreateNodeResponse;
use Aurora\Application\ContentRepository\Port\NodeTypeRepository;
use Aurora\Application\ContentRepository\Port\WorkspaceRepository;
use Aurora\Application\Contract\TransactionBoundary;
use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodeId;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;

final readonly class CreateNodeHandler
{
    public function __construct(
        private WorkspaceRepository $workspaceRepository,
        private NodeTypeRepository $nodeTypeRepository,
        private TransactionBoundary $tx,
    ) {
    }

    public function __invoke(CreateNodeRequest $request): CreateNodeResponse
    {
        return $this->tx->run(function () use ($request): CreateNodeResponse {
            $ws = $this->workspaceRepository->get(
                WorkspaceId::fromString($request->workspaceId),
                new DimensionSet($request->dimensions),
            );

            $newNodeId = NodeId::fromString($request->newNodeId);
            $nodeType = $this->nodeTypeRepository->get($request->nodeType);
            $ws->createNode(
                $newNodeId,
                $nodeType,
                $request->properties,
                NodeId::fromString($request->parentId),
                $request->segment
            );

            $n = $ws->get($newNodeId);

            return new CreateNodeResponse(
                (string) $n->id,
                (string) $n->path,
            );
        });
    }
}
