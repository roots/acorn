<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Session\SessionManager;
use Illuminate\Session\Store;
use Roots\Acorn\Session\Middleware\StartSession;
use Roots\Acorn\Tests\Test\TestCase;

uses(TestCase::class);

function createStartSessionFixtures(): array
{
    $store = Mockery::mock(Store::class);
    $store->shouldReceive('start')->andReturnSelf();
    $store->shouldReceive('setRequestOnHandler');
    $store->shouldReceive('getId')->andReturn('test-session-id');
    $store->shouldReceive('getName')->andReturn('laravel_session');
    $store->shouldReceive('setPreviousUrl');
    $store->shouldReceive('setPreviousRoute');
    $store->shouldReceive('setId');

    $manager = Mockery::mock(SessionManager::class);
    $manager->shouldReceive('shouldBlock')->andReturn(false);
    $manager->shouldReceive('driver')->andReturn($store);
    $manager->shouldReceive('getSessionConfig')->andReturn([
        'driver' => 'file',
        'lifetime' => 120,
        'lottery' => [0, 100],
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'http_only' => true,
        'same_site' => 'lax',
        'partitioned' => false,
        'expire_on_close' => false,
    ]);

    return [$manager, $store];
}

it('should save session immediately for non-wordpress routes', function () {
    [$manager, $store] = createStartSessionFixtures();

    $store->shouldReceive('save')->once();

    $middleware = new StartSession($manager);

    $request = Request::create('/contact/send', 'POST');
    $route = new Route('POST', 'contact/send', fn () => 'ok');
    $route->name('contact.send');
    $request->setRouteResolver(fn () => $route);

    $middleware->handle($request, fn ($req) => new Response('ok'));
});

it('should defer session save for wordpress routes until shutdown', function () {
    [$manager, $store] = createStartSessionFixtures();

    $middleware = new StartSession($manager);

    $request = Request::create('/some-page', 'GET');
    $route = new Route('GET', '{any?}', fn () => 'ok');
    $route->name('wordpress');
    $request->setRouteResolver(fn () => $route);

    // Session save should NOT be called during handle()
    $store->shouldNotReceive('save');

    $middleware->handle($request, fn ($req) => new Response('ok'));

    // A shutdown hook should have been registered
    expect($this->filters)->toHaveKey('shutdown');

    // Now allow save and invoke the deferred shutdown callback
    $store->shouldReceive('save')->once();

    $shutdownCallbacks = $this->filters['shutdown'];
    foreach ($shutdownCallbacks as $callback) {
        $callback();
    }
});
