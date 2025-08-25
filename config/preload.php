<?php

declare(strict_types=1);

/*
 * This file is part of Aurora Core.
 *
 * (c) The Aurora Core contributors
 * License: MIT
 */

if (file_exists(dirname(__DIR__).'/var/cache/prod/Aurora_KernelProdContainer.preload.php')) {
    require dirname(__DIR__).'/var/cache/prod/Aurora_KernelProdContainer.preload.php';
}
