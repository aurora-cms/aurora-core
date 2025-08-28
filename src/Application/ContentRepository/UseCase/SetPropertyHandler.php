<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Application\ContentRepository\UseCase;

use Aurora\Application\ContentRepository\Dto\SetPropertyRequest;
use Aurora\Application\ContentRepository\Dto\SetPropertyResponse;
use Aurora\Application\ContentRepository\Port\WorkspaceRepository;
use Aurora\Application\Contract\TransactionBoundary;
use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodeId;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;

final readonly class SetPropertyHandler
{
    public function __construct(
        private WorkspaceRepository $repo,
        private TransactionBoundary $tx,
    ) {
    }

    public function __invoke(SetPropertyRequest $r): SetPropertyResponse
    {
        return $this->tx->run(function () use ($r): SetPropertyResponse {
            $ws = $this->repo->get(
                WorkspaceId::fromString($r->workspaceId),
                new DimensionSet($r->dimensions),
            );
            $nid = NodeId::fromString($r->nodeId);
            $ws->setProperty($nid, $r->propertyName, $r->propertyValue);
            $this->repo->save($ws);

            return new SetPropertyResponse((string) $nid, $r->propertyName, $r->propertyValue);
        });
    }
}
