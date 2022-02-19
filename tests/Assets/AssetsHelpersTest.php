<?php

use Roots\Acorn\Assets\Contracts\Manifest as ManifestContract;
use Roots\Acorn\Tests\Test\TestCase;

use function Spatie\Snapshots\assertMatchesSnapshot;
use function Roots\asset;
use function Roots\bundle;

uses(TestCase::class);

it('asset() can access the default manifest', function () {
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
                'editor' => [
                    'path' => $this->fixture('bud_single_runtime/public/editor'),
                    'url' => 'https://k.jo/editor',
                    'assets' => $this->fixture('bud_multi_compiler/public/editor/manifest.json'),
                    'bundles' => $this->fixture('bud_multi_compiler/public/editor/entrypoints.json'),
                ],
            ]
        ]
    ]));
    $app->register(\Roots\Acorn\Assets\AssetsServiceProvider::class);

    assertMatchesSnapshot(asset('app.js')->uri());
});

it('asset() can access a specified manifest', function () {
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
                'editor' => [
                    'path' => $this->fixture('bud_single_runtime/public/editor'),
                    'url' => 'https://k.jo/editor',
                    'assets' => $this->fixture('bud_multi_compiler/public/editor/manifest.json'),
                    'bundles' => $this->fixture('bud_multi_compiler/public/editor/entrypoints.json'),
                ],
            ]
        ]
    ]));
    $app->register(\Roots\Acorn\Assets\AssetsServiceProvider::class);

    assertMatchesSnapshot(asset('editor.js', 'editor')->uri());
});

it('bundle() can access the default manifest', function () {
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
                'editor' => [
                    'path' => $this->fixture('bud_single_runtime/public/editor'),
                    'url' => 'https://k.jo/editor',
                    'assets' => $this->fixture('bud_multi_compiler/public/editor/manifest.json'),
                    'bundles' => $this->fixture('bud_multi_compiler/public/editor/entrypoints.json'),
                ],
            ]
        ]
    ]));
    $app->register(\Roots\Acorn\Assets\AssetsServiceProvider::class);

    assertMatchesSnapshot(bundle('app')->js()->toJson());
});

it('bundle() can access a specified manifest', function () {
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
                'editor' => [
                    'path' => $this->fixture('bud_single_runtime/public/editor'),
                    'url' => 'https://k.jo/editor',
                    'assets' => $this->fixture('bud_multi_compiler/public/editor/manifest.json'),
                    'bundles' => $this->fixture('bud_multi_compiler/public/editor/entrypoints.json'),
                ],
            ]
        ]
    ]));
    $app->register(\Roots\Acorn\Assets\AssetsServiceProvider::class);

    assertMatchesSnapshot(bundle('editor', 'editor')->js()->toJson());
});
