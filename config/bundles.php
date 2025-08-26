<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

return [
    Aurora\Domain\AuroraDomainBundle::class => ['all' => true],
    Aurora\Application\AuroraApplicationBundle::class => ['all' => true],
    Aurora\Infrastructure\AuroraInfrastructureBundle::class => ['all' => true],
    Aurora\Interface\Cli\AuroraCliBundle::class => ['all' => true],
    Aurora\Interface\Http\AuroraHttpBundle::class => ['all' => true],
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
];
