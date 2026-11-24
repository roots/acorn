<?php

use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Facade;
use Roots\Acorn\Application;
use Roots\Acorn\Assets\AssetsServiceProvider;
use Roots\Acorn\Assets\Vite;
use Roots\Acorn\Tests\Test\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Facade::setFacadeApplication(new Application());
});

/**
 * Resolve Vite::assetPath() for a manifest served from the given URL.
 */
function viteAssetPath(array $paths, string $manifestUrl): string
{
    $app = new Application();
    $app->singleton(
        'config',
        fn () => new Repository([
            'assets' => [
                'default' => 'app',
                'manifests' => [
                    'app' => [
                        'path' => $paths['path'],
                        'url' => $manifestUrl,
                        'assets' => $paths['assets'],
                        'bundles' => $paths['bundles'],
                    ],
                ],
            ],
        ]),
    );
    $app->register(AssetsServiceProvider::class);

    $vite = $app->make(Vite::class);

    return (new ReflectionMethod($vite, 'assetPath'))->invoke($vite, 'app.js');
}

it('Vite::assetPath() resolves the host for :dataset', function (
    bool $multisite,
    string $homeUrl,
    string $manifestUrl,
    string $expected,
) {
    $this->stub('is_multisite', fn () => $multisite);
    $this->stub('home_url', fn () => $homeUrl);
    $this->stub('network_home_url', fn () => 'https://example.com');

    expect(viteAssetPath([
        'path' => $this->fixture('bud_single_runtime/public/app'),
        'assets' => $this->fixture('bud_multi_compiler/public/app/manifest.json'),
        'bundles' => $this->fixture('bud_multi_compiler/public/app/entrypoints.json'),
    ], $manifestUrl))
        ->toBe($expected);
})->with([
    'single-site' => [
        false,
        'https://example.com',
        'https://example.com/app',
        'https://example.com/app/public/app.9876543210.js',
    ],
    'subdomain multisite' => [
        true,
        'https://sub.example.com',
        'https://example.com/app',
        'https://sub.example.com/app/public/app.9876543210.js',
    ],
    'subdirectory multisite (no subsite slug)' => [
        true,
        'https://example.com/subsite',
        'https://example.com/app',
        'https://example.com/app/public/app.9876543210.js',
    ],
    'CDN host (unchanged)' => [
        true,
        'https://sub.example.com',
        'https://cdn.example.net/app',
        'https://cdn.example.net/app/public/app.9876543210.js',
    ],
]);
