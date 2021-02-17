<?php

namespace Roots\Acorn\Tests;

use Mockery;
use Mockery\MockInterface;
use Spatie\TemporaryDirectory\TemporaryDirectory;

function plugin_entrypoint()
{
    return __DIR__ . '/../acorn.php';
}

function temp($path = null)
{
    static $temp;

    if (! $temp) {
        $temp = (new TemporaryDirectory())->create();

        define('ABSPATH', $temp->path('wp'));

        register_shutdown_function(function () use ($temp) {
            $temp->delete();
        });
    }

    if ($path) {
        return $temp->path($path);
    }

    return $temp;
}

function mock(string $class): MockInterface
{
    return Mockery::mock($class);
}
