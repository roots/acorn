<?php

use Roots\Acorn\Assets\Contracts\Manifest as ManifestContract;
use Roots\Acorn\Assets\Contracts\ManifestNotFoundException;
use Roots\Acorn\Assets\Manager;
use Roots\Acorn\Assets\Manifest;
use Roots\Acorn\Tests\Test\TestCase;

use function Spatie\Snapshots\assertMatchesSnapshot;

uses(TestCase::class);

it('creates a manifest', function () {
    $assets = new Manager();

    $manifest = $assets->manifest('theme', [
        'path' => $this->fixture('bud_single_runtime'),
        'url' => 'https://k.jo',
        'assets' => $this->fixture('bud_single_runtime/public/manifest.json'),
    ]);

    expect($manifest)->toBeInstanceOf(ManifestContract::class);
});

it('registers a manifest', function () {
    $assets = new Manager();

    $assets->register('theme', new Manifest(
        $this->fixture('bud_single_runtime'),
        'https://k.jo',
        [],
    ));

    expect($assets->manifest('theme'))->toBeInstanceOf(ManifestContract::class);
});

it('throws an error if an assets manifest does not exist', function () {
    $assets = new Manager([
        'manifests' => [
            'theme' => [
                'path' => $this->fixture('bud_single_runtime'),
                'url' => 'https://k.jo',
                'assets' => __DIR__ . '/does/not/exist/manifest.json',
            ]
        ]
    ]);

    $assets->manifest('theme')->asset('app.css')->uri();
})->throws(ManifestNotFoundException::class);

it('reads an assets manifest', function () {
    $assets = new Manager([
        'manifests' => [
            'theme' => [
                'path' => $this->fixture('bud_single_runtime'),
                'url' => 'https://k.jo',
                'assets' => $this->fixture('bud_single_runtime/public/manifest.json'),
            ]
        ]
    ]);

    assertMatchesSnapshot($assets->manifest('theme')->asset('app.css')->uri());
    assertMatchesSnapshot($assets->manifest('theme')->asset('app.js')->uri());
});

it('reads multiple manifests', function () {
    $assets = new Manager([
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
    ]);

    assertMatchesSnapshot($assets->manifest('app')->asset('app.js')->uri());
    assertMatchesSnapshot($assets->manifest('editor')->asset('editor.js')->uri());
});

it('throws an error if a bundles manifest does not exist', function () {
    $assets = new Manager([
        'manifests' => [
            'theme' => [
                'path' => $this->fixture('bud_single_runtime'),
                'url' => 'https://k.jo',
                'bundles' => __DIR__ . '/does/not/exist/entrypoints.json',
            ]
        ]
    ]);

    $assets->manifest('theme')->bundle('app')->js()->toJson();
})->throws(ManifestNotFoundException::class);

it('reads a bundles manifest', function () {
    $assets = new Manager([
        'manifests' => [
            'theme' => [
                'path' => $this->fixture('bud_single_runtime'),
                'url' => 'https://k.jo',
                'bundles' => $this->fixture('bud_single_runtime/public/entrypoints.json'),
            ]
        ]
    ]);

    assertMatchesSnapshot($assets->manifest('theme')->bundle('app')->js()->toJson());
    assertMatchesSnapshot($assets->manifest('theme')->bundle('editor')->js()->toJson());
    assertMatchesSnapshot($assets->manifest('theme')->bundle('editor')->js()->toJson());
});

it('reads a mix manifest', function () {
    $assets = new Manager([
        'manifests' => [
            'theme' => [
                'path' => $this->fixture('mix_no_bundle/public'),
                'url' => 'https://k.jo/public',
                'assets' => $this->fixture('mix_no_bundle/public/mix-manifest.json'),
            ]
        ]
    ]);

    assertMatchesSnapshot($assets->manifest('theme')->asset('styles/app.css')->uri());
    assertMatchesSnapshot($assets->manifest('theme')->asset('scripts/app.js')->uri());
});
