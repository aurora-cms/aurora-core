<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Application\ContentRepository\UseCase;

use Aurora\Application\ContentRepository\Dto\DeleteNodeRequest;
use Aurora\Application\ContentRepository\Dto\DeleteNodeResponse;
use Aurora\Application\ContentRepository\Port\WorkspaceRepository;
use Aurora\Application\Contract\TransactionBoundary;
use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodeId;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;

final readonly class DeleteNodeHandler
{
    public function __construct(
        private WorkspaceRepository $workspaceRepository,
        private TransactionBoundary $tx,
    ) {
    }

    public function __invoke(DeleteNodeRequest $request): DeleteNodeResponse
    {
        return $this->tx->run(function () use ($request): DeleteNodeResponse {
            $ws = $this->workspaceRepository->get(
                WorkspaceId::fromString($request->workspaceId),
                new DimensionSet($request->dimensions),
            );
            $id = NodeId::fromString($request->nodeId);
            $ws->get($id); // ensure it exists

            /** @var string[] $removed */
            $removed = [];

            if ($request->cascade) {
                $stack = [NodeId::fromString($request->nodeId)];
                while ($stack) {
                    $current = array_pop($stack);
                    $removed[] = (string) $current;
                    foreach ($ws->childrenOf($current) as $child) {
                        $stack[] = $child->id;
                    }
                }
            }
            $ws->remove($id, cascade: $request->cascade);
            $this->workspaceRepository->save($ws);

            return new DeleteNodeResponse(
                nodeId: $request->nodeId,
                cascade: $request->cascade,
                deletedNodeIds: $removed,
            );
        });
    }
}
