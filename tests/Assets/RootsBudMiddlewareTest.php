<?php

use Roots\Acorn\Assets\Middleware\RootsBudMiddleware;
use Roots\Acorn\Tests\Test\TestCase;

use function Spatie\Snapshots\assertMatchesSnapshot;

uses(TestCase::class);

it('skips url modification if hmr file is absent', function () {
    $middleware = new RootsBudMiddleware('http://localhost:3000/');

    $config = $middleware->handle([
        'path' => $this->fixture('bud_single_runtime/public'),
        'url' => 'https://k.jo/public',
    ]);

    assertMatchesSnapshot($config['url']);
});

it('skips url modification if dev origin is invalid', function () {
    $middleware = new RootsBudMiddleware('http://this-does-not-match/');

    $config = $middleware->handle([
        'path' => $this->fixture('bud_single_runtime_hmr/public'),
        'url' => 'https://k.jo/public',
    ]);

    assertMatchesSnapshot($config['url']);
});

it('modifies url when hmr file is present with matching dev origin', function () {
    $middleware = new RootsBudMiddleware('http://localhost:3000/');

    $config = $middleware->handle([
        'path' => $this->fixture('bud_single_runtime_hmr/public'),
        'url' => 'https://k.jo/public',
    ]);

    assertMatchesSnapshot($config['url']);
});
