<?php

namespace Roots\Acorn\Tests;

use Mockery;
use Mockery\MockInterface;
use Spatie\TemporaryDirectory\TemporaryDirectory;

function acorn_root(?string $path = null)
{
    return dirname(__DIR__) . ($path ? "/{$path}" : '');
}

function plugin_entrypoint()
{
    return __DIR__ . '/../acorn.php';
}

/**
 * Get a temporary directory
 *
 * @param string|null $path
 * @return string|TemporaryDirectory
 */
function temp(?string $path = null)
{
    static $temp;

    if (! $temp) {
        $temp = (new TemporaryDirectory())->create();

        register_shutdown_function(function () use ($temp) {
            $temp->delete();
        });
    }

    if ($path !== null) {
        return $temp->path($path);
    }

    return $temp;
}

function mock(string $class): MockInterface
{
    return Mockery::mock($class);
}
