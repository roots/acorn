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

it('Vite::assetPath() returns the asset URI unchanged on single-site', function () {
    $this->stub('is_multisite', fn () => false);
    $this->stub('home_url', fn () => 'https://example.com');

    $app = new Application();
    $app->singleton(
        'config',
        fn () => new Repository([
            'assets' => [
                'default' => 'app',
                'manifests' => [
                    'app' => [
                        'path' => $this->fixture('bud_single_runtime/public/app'),
                        'url' => 'https://k.jo/app',
                        'assets' => $this->fixture('bud_multi_compiler/public/app/manifest.json'),
                        'bundles' => $this->fixture('bud_multi_compiler/public/app/entrypoints.json'),
                    ],
                ],
            ],
        ]),
    );
    $app->register(AssetsServiceProvider::class);

    $vite = $app->make(Vite::class);
    $method = new ReflectionMethod($vite, 'assetPath');

    expect($method->invoke($vite, 'app.js'))->toBe('https://k.jo/app/public/app.9876543210.js');
});

it('Vite::assetPath() rewrites the host for subdomain multisite', function () {
    $this->stub('is_multisite', fn () => true);
    $this->stub('home_url', fn () => 'https://sub.example.com');

    $app = new Application();
    $app->singleton(
        'config',
        fn () => new Repository([
            'assets' => [
                'default' => 'app',
                'manifests' => [
                    'app' => [
                        'path' => $this->fixture('bud_single_runtime/public/app'),
                        'url' => 'https://k.jo/app',
                        'assets' => $this->fixture('bud_multi_compiler/public/app/manifest.json'),
                        'bundles' => $this->fixture('bud_multi_compiler/public/app/entrypoints.json'),
                    ],
                ],
            ],
        ]),
    );
    $app->register(AssetsServiceProvider::class);

    $vite = $app->make(Vite::class);
    $method = new ReflectionMethod($vite, 'assetPath');

    expect($method->invoke($vite, 'app.js'))->toBe('https://sub.example.com/app/public/app.9876543210.js');
});

it('Vite::assetPath() does not inject the subsite slug for subdirectory multisite', function () {
    $this->stub('is_multisite', fn () => true);
    $this->stub('home_url', fn () => 'https://example.com/subsite');

    $app = new Application();
    $app->singleton(
        'config',
        fn () => new Repository([
            'assets' => [
                'default' => 'app',
                'manifests' => [
                    'app' => [
                        'path' => $this->fixture('bud_single_runtime/public/app'),
                        'url' => 'https://k.jo/app',
                        'assets' => $this->fixture('bud_multi_compiler/public/app/manifest.json'),
                        'bundles' => $this->fixture('bud_multi_compiler/public/app/entrypoints.json'),
                    ],
                ],
            ],
        ]),
    );
    $app->register(AssetsServiceProvider::class);

    $vite = $app->make(Vite::class);
    $method = new ReflectionMethod($vite, 'assetPath');

    expect($method->invoke($vite, 'app.js'))->toBe('https://example.com/app/public/app.9876543210.js');
});
