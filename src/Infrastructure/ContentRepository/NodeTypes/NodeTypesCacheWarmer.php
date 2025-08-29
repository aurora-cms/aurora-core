<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Infrastructure\ContentRepository\NodeTypes;

use Aurora\Domain\ContentRepository\Type\NodeType;
use Aurora\Domain\ContentRepository\Type\PropertyDefinition;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Warms cache with resolved node types to avoid re-parsing at runtime.
 */
final class NodeTypesCacheWarmer implements CacheWarmerInterface
{
    public function __construct(
        private readonly DefinitionLoader $loader,
        private readonly NodeTypeResolver $resolver,
        private readonly ?string $cacheFile = null,
    ) {
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        $resolved = $this->resolver->resolve($this->loader->load());
        $payload = [];
        foreach ($resolved as $type) {
            $payload[] = $this->exportNodeType($type);
        }

        $target = $this->cacheFile ?: ($cacheDir.DIRECTORY_SEPARATOR.'aurora_node_types.json');
        $dir = \dirname($target);
        if (!is_dir($dir)) {
            @mkdir($dir, recursive: true);
        }
        file_put_contents($target, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return [$target];
    }

    /**
     * @return array{name: string, properties: list<array{name: string, type: string, nullable: bool, multiple: bool}>}
     */
    private function exportNodeType(NodeType $type): array
    {
        $rp = new \ReflectionProperty(NodeType::class, 'definitions');
        $rp->setAccessible(true);
        /** @var array<string, PropertyDefinition> $defs */
        $defs = $rp->getValue($type);

        $props = [];
        foreach ($defs as $pd) {
            $props[] = [
                'name' => $pd->name,
                'type' => $pd->type->value,
                'nullable' => $pd->nullable,
                'multiple' => $pd->multiple,
            ];
        }

        return [
            'name' => $type->name,
            'properties' => $props,
        ];
    }
}
