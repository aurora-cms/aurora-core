<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Infrastructure\Persistence\ContentRepository\Doctrine;

use Aurora\Application\ContentRepository\Port\NodeTypeRepository;
use Aurora\Domain\ContentRepository\Exception\NodeTypeNotFound;
use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Domain\ContentRepository\Type\PropertyDefinition;
use Aurora\Domain\ContentRepository\Type\PropertyType;
use Aurora\Infrastructure\Persistence\ContentRepository\Doctrine\Entity\NodeTypeRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineNodeTypeRepository implements NodeTypeRepository
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function register(NodeType $type): void
    {
        $rec = $this->em->find(NodeTypeRecord::class, $type->name) ?? new NodeTypeRecord();
        $rec->name = $type->name;
        $rec->properties_schema = array_values(array_map(
            static function (PropertyDefinition $def): array {
                return [
                    'name' => $def->name,
                    'type' => $def->type->value,
                    'nullable' => $def->nullable,
                    'multiple' => $def->multiple,
                ];
            },
            $type->definitions()
        ));
        $this->em->persist($rec);
        $this->em->flush();
    }

    public function get(string $name): NodeType
    {
        /** @var NodeTypeRecord|null $rec */
        $rec = $this->em->find(NodeTypeRecord::class, $name);
        if (null === $rec) {
            throw new NodeTypeNotFound(\sprintf('Node type "%s" is not registered.', $name));
        }

        return $this->hydrate($rec);
    }

    public function all(): array
    {
        $list = $this->em->getRepository(NodeTypeRecord::class)->findAll();

        return array_map(fn (NodeTypeRecord $r) => $this->hydrate($r), $list);
    }

    public function has(string $name): bool
    {
        return null !== $this->em->find(NodeTypeRecord::class, $name);
    }

    private function hydrate(NodeTypeRecord $rec): NodeType
    {
        $defs = [];
        /**
         * @var list<array{
         *     name: string,
         *     type: string,
         *     nullable?: bool,
         *     multiple?: bool
         * }> $schema
         */
        $schema = $rec->properties_schema ?? [];
        foreach ($schema as $raw) {
            /** @var array{name: string, type: string, nullable?: bool, multiple?: bool} $raw */
            $defs[] = new PropertyDefinition(
                $raw['name'],
                PropertyType::from($raw['type']),
                (bool) ($raw['nullable'] ?? false),
                (bool) ($raw['multiple'] ?? false),
            );
        }

        return new NodeType($rec->name, $defs);
    }
}
