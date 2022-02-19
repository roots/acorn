<?php

use Roots\Acorn\Assets\Contracts\Manifest as ManifestContract;
use Roots\Acorn\Assets\Manager;
use Roots\Acorn\Tests\Test\TestCase;

use function Spatie\Snapshots\assertMatchesSnapshot;

uses(TestCase::class);

it('registers an asset manager', function () {
    $app = new \Roots\Acorn\Application();
    $app->singleton('config', fn () => new \Illuminate\Config\Repository());
    $app->register(\Roots\Acorn\Assets\AssetsServiceProvider::class);

    expect($app->make('assets'))->toBeInstanceOf(Manager::class);
});

it('registers a default manifest', function () {
    $app = new \Roots\Acorn\Application();
    $app->singleton('config', fn () => new \Illuminate\Config\Repository([
        'assets' => [
            'default' => 'app',
            'manifests' => [
                'app' => [
                    'path' => $this->fixture('bud_single_runtime/public/app'),
                    'url' => 'https://k.jo/app',
                    'assets' => $this->fixture('bud_multi_compiler/public/app/manifest.json'),
                    'bundles' => $this->fixture('bud_multi_compiler/public/app/entrypoints.json'),
                ],
            ]
        ]
    ]));
    $app->register(\Roots\Acorn\Assets\AssetsServiceProvider::class);

    expect($app->make('assets.manifest'))->toBeInstanceOf(ManifestContract::class);
    assertMatchesSnapshot($app->make('assets.manifest')->asset('app.js')->uri());
});
