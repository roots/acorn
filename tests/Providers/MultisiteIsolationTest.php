<?php

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Roots\Acorn\Providers\AcornServiceProvider;
use Roots\Acorn\Tests\Test\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->stub('is_multisite', fn () => $this->isMultisite);
    $this->stub('get_current_blog_id', fn () => $this->blogId);

    $this->isMultisite = false;
    $this->blogId = 1;
});

function invokeConfigureMultisite(AcornServiceProvider $provider): void
{
    $method = new ReflectionMethod($provider, 'configureMultisite');
    $method->invoke($provider);
}

function createProviderWithConfig(array $config = []): array
{
    $container = new Container;

    $container->instance('config', new Repository(array_merge([
        'cache' => ['prefix' => 'acorn_cache_'],
        'session' => ['cookie' => 'acorn_session'],
    ], $config)));

    $provider = new AcornServiceProvider($container);

    return [$provider, $container];
}

it('should prefix cache keys per blog on multisite', function () {
    $this->isMultisite = true;
    $this->blogId = 3;

    [$provider, $container] = createProviderWithConfig();
    invokeConfigureMultisite($provider);

    expect($container->make('config')->get('cache.prefix'))
        ->toBe('acorn_cache_blog_3_');
});

it('should set unique session cookie per blog on multisite', function () {
    $this->isMultisite = true;
    $this->blogId = 5;

    [$provider, $container] = createProviderWithConfig();
    invokeConfigureMultisite($provider);

    expect($container->make('config')->get('session.cookie'))
        ->toBe('acorn_session_5');
});

it('should not modify config on single-site', function () {
    $this->isMultisite = false;

    [$provider, $container] = createProviderWithConfig();
    invokeConfigureMultisite($provider);

    expect($container->make('config')->get('cache.prefix'))->toBe('acorn_cache_');
    expect($container->make('config')->get('session.cookie'))->toBe('acorn_session');
});

it('should isolate cache prefix between blogs', function () {
    $this->isMultisite = true;

    $this->blogId = 1;
    [$provider1, $container1] = createProviderWithConfig();
    invokeConfigureMultisite($provider1);

    $this->blogId = 2;
    [$provider2, $container2] = createProviderWithConfig();
    invokeConfigureMultisite($provider2);

    $prefix1 = $container1->make('config')->get('cache.prefix');
    $prefix2 = $container2->make('config')->get('cache.prefix');

    expect($prefix1)->not->toBe($prefix2);
    expect($prefix1)->toBe('acorn_cache_blog_1_');
    expect($prefix2)->toBe('acorn_cache_blog_2_');
});

it('should update config when blog is switched', function () {
    $this->isMultisite = true;
    $this->blogId = 1;

    [$provider, $container] = createProviderWithConfig();
    invokeConfigureMultisite($provider);

    expect($container->make('config')->get('cache.prefix'))->toBe('acorn_cache_blog_1_');
    expect($container->make('config')->get('session.cookie'))->toBe('acorn_session_1');

    // Simulate switch_to_blog(4)
    $this->blogId = 4;
    do_action('switch_blog');

    expect($container->make('config')->get('cache.prefix'))->toBe('acorn_cache_blog_4_');
    expect($container->make('config')->get('session.cookie'))->toBe('acorn_session_4');
});
