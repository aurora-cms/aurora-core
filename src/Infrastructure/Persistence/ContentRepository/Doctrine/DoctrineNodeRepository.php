<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Infrastructure\Persistence\ContentRepository\Doctrine;

use Aurora\Application\ContentRepository\Port\NodeRepository as NodeRepositoryPort;
use Aurora\Application\ContentRepository\Port\NodeTypeRepository;
use Aurora\Domain\ContentRepository\Exception\NodeNotFound;
use Aurora\Domain\ContentRepository\Node;
use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Domain\ContentRepository\Value\DimensionSet;
use Aurora\Domain\ContentRepository\Value\NodeId;
use Aurora\Domain\ContentRepository\Value\NodePath;
use Aurora\Domain\ContentRepository\Value\WorkspaceId;
use Aurora\Infrastructure\Persistence\ContentRepository\Doctrine\Entity\NodeRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineNodeRepository implements NodeRepositoryPort
{
    public function __construct(
        private EntityManagerInterface $em,
        private NodeTypeRepository $nodeTypes,
    ) {
    }

    public function save(Node $node, ?NodeId $parentId = null, ?string $segment = null): void
    {
        $rec = $this->em->find(NodeRecord::class, (string) $node->id) ?? new NodeRecord();
        $rec->id = (string) $node->id;
        $rec->workspace_id = (string) $node->workspaceId;
        $rec->dimensions = (string) $node->dimensionSet;
        $rec->parent_id = $parentId ? (string) $parentId : $rec->parent_id;
        $rec->segment = null !== $segment ? strtolower($segment) : $rec->segment;
        $rec->path = (string) $node->path;
        $rec->type = $node->type->name;
        $rec->properties = $node->properties;
        $this->em->persist($rec);
        $this->em->flush();
    }

    public function remove(NodeId $id, bool $cascade = false): void
    {
        /** @var NodeRecord|null $rec */
        $rec = $this->em->find(NodeRecord::class, (string) $id);
        if (null === $rec) {
            return;
        }
        if ($cascade) {
            $this->removeSubtree($rec->id);
        } else {
            $this->em->remove($rec);
        }
        $this->em->flush();
    }

    public function get(NodeId $id): Node
    {
        /** @var NodeRecord|null $rec */
        $rec = $this->em->find(NodeRecord::class, (string) $id);
        if (null === $rec) {
            throw new NodeNotFound('Node not found: '.$id);
        }

        return $this->hydrate($rec);
    }

    public function getByPath(WorkspaceId $workspaceId, DimensionSet $dimensions, NodePath $path): Node
    {
        /** @var NodeRecord|null $rec */
        $rec = $this->em->getRepository(NodeRecord::class)->findOneBy([
            'workspace_id' => (string) $workspaceId,
            'dimensions' => (string) $dimensions,
            'path' => (string) $path,
        ]);
        if (null === $rec) {
            throw new NodeNotFound('Node not found at path: '.$path);
        }

        return $this->hydrate($rec);
    }

    public function childrenOf(NodeId $parentId): array
    {
        $list = $this->em->getRepository(NodeRecord::class)->findBy(['parent_id' => (string) $parentId]);

        return array_map(fn (NodeRecord $r) => $this->hydrate($r), $list);
    }

    private function removeSubtree(string $id): void
    {
        $repo = $this->em->getRepository(NodeRecord::class);
        /** @var NodeRecord[] $children */
        $children = $repo->findBy(['parent_id' => $id]);
        foreach ($children as $child) {
            $this->removeSubtree($child->id);
        }
        /** @var NodeRecord|null $rec */
        $rec = $this->em->find(NodeRecord::class, $id);
        if (null !== $rec) {
            $this->em->remove($rec);
        }
    }

    private function hydrate(NodeRecord $r): Node
    {
        $type = $this->resolveType($r->type);
        /** @var array<string, string> $dims */
        $dims = $this->parseDimensionsString($r->dimensions);

        return new Node(
            NodeId::fromString($r->id),
            WorkspaceId::fromString($r->workspace_id),
            new DimensionSet($dims),
            $type,
            NodePath::fromString($r->path),
            $r->properties ?? []
        );
    }

    private function resolveType(string $name): NodeType
    {
        return $this->nodeTypes->get($name);
    }

    /**
     * Parse DimensionSet string form back to associative array of dimensions.
     * Example: "{channel=web;locale=en_US}" => ['channel' => 'web', 'locale' => 'en_US']
     * Empty set "{}" => [].
     */
    /**
     * @return array<string, string>
     */
    private function parseDimensionsString(string $str): array
    {
        $trim = trim($str);
        if ('{}' === $trim) {
            return [];
        }
        $inner = trim($trim, '{}');
        if ('' === $inner) {
            return [];
        }
        /** @var array<string, string> $result */
        $result = [];
        foreach (explode(';', $inner) as $pair) {
            [$k, $v] = array_map('trim', explode('=', $pair, 2));
            if ('' !== $k) {
                $result[$k] = $v;
            }
        }

        return $result;
    }
}
