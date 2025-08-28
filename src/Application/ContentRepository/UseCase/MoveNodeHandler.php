<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Application\ContentRepository\UseCase;

use Aurora\Application\ContentRepository\Dto\MoveNodeRequest;
use Aurora\Application\ContentRepository\Dto\MoveNodeResponse;
use Aurora\Application\ContentRepository\Port\WorkspaceRepository;
use Aurora\Application\Contract\TransactionBoundary;
use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodeId;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;

/**
 * Handles the move node use case within a workspace.
 *
 * This handler moves a node to a new parent within the specified workspace and dimension set,
 * persisting the change and returning the updated node information.
 */
final readonly class MoveNodeHandler
{
    /**
     * Constructor for MoveNodeHandler.
     *
     * @param WorkspaceRepository $repo repository for workspace operations
     * @param TransactionBoundary $tx   transaction boundary for atomic operations
     */
    public function __construct(
        private WorkspaceRepository $repo,
        private TransactionBoundary $tx,
    ) {
    }

    /**
     * Invokes the move node operation.
     *
     * @param MoveNodeRequest $request the request containing node and workspace details
     *
     * @return MoveNodeResponse the response with updated node information
     */
    public function __invoke(MoveNodeRequest $request): MoveNodeResponse
    {
        return $this->tx->run(function () use ($request): MoveNodeResponse {
            $ws = $this->repo->get(
                WorkspaceId::fromString($request->workspaceId),
                new DimensionSet($request->dimensions),
            );

            $nodeId = NodeId::fromString($request->nodeId);
            $ws->move($nodeId, NodeId::fromString($request->newParentId));
            $this->repo->save($ws);

            $n = $ws->get($nodeId);

            return new MoveNodeResponse((string)$n->id, (string)$n->path);
        });
    }
}
