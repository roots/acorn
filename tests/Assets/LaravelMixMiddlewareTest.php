<?php

use Roots\Acorn\Assets\Middleware\LaravelMixMiddleware;
use Roots\Acorn\Tests\Test\TestCase;

use function Spatie\Snapshots\assertMatchesSnapshot;

uses(TestCase::class);

it('skips url modification if hot file is absent', function () {
    $middleware = new LaravelMixMiddleware();

    $config = $middleware->handle([
        'path' => $this->fixture('mix_no_bundle/public'),
        'url' => 'https://k.jo/public',
    ]);

    assertMatchesSnapshot($config['url']);
});

it('modifies url when hot file is present', function () {
    $middleware = new LaravelMixMiddleware();

    $config = $middleware->handle([
        'path' => $this->fixture('mix_no_bundle_hmr/public'),
        'url' => 'https://k.jo/public',
    ]);

    assertMatchesSnapshot($config['url']);
});
