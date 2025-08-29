<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Infrastructure\Persistence\ContentRepository\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'cr_nodes')]
#[ORM\Index(columns: ['workspace_id'], name: 'idx_cr_nodes_workspace')]
#[ORM\Index(columns: ['type'], name: 'idx_cr_nodes_type')]
#[ORM\Index(columns: ['path'], name: 'idx_cr_nodes_path')]
#[ORM\Index(columns: ['parent_id'], name: 'idx_cr_nodes_parent')]
class NodeRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 64, unique: true)]
    public string $id;

    #[ORM\Column(type: 'string', length: 64)]
    public string $workspace_id;

    // Canonicalized string form from DimensionSet::__toString
    #[ORM\Column(type: 'string', length: 512)]
    public string $dimensions;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    public ?string $parent_id = null;

    // Lowercase path segment name of this node
    #[ORM\Column(type: 'string', length: 190, nullable: true)]
    public ?string $segment = null;

    // Canonical absolute path (e.g. "/", "/a/b")
    #[ORM\Column(type: 'string', length: 1024)]
    public string $path;

    // Node type name
    #[ORM\Column(type: 'string', length: 190)]
    public string $type;

    // JSON object of properties
    #[ORM\Column(type: 'json', nullable: true)]
    public ?array $properties = null;
}

