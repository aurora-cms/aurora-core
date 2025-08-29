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
#[ORM\Table(name: 'cr_node_types')]
class NodeTypeRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 190)]
    public string $name;

    // JSON array: [{name, type, nullable, multiple}]
    #[ORM\Column(type: 'json', nullable: true)]
    public ?array $properties_schema = null;
}

