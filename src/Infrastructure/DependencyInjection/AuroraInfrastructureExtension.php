<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

namespace Aurora\Infrastructure\DependencyInjection;

use Aurora\Infrastructure\Persistence\ContentRepository\ConfigNodeTypeRepository;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class AuroraInfrastructureExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        // Prefer config-backed repository by default
        $container->setAlias('Aurora\\Application\\ContentRepository\\Port\\NodeTypeRepository', ConfigNodeTypeRepository::class);
    }
}
