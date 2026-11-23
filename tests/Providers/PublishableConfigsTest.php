<?php

use Illuminate\Config\Repository;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\PackageManifest;
use Illuminate\Hashing\HashServiceProvider;
use Illuminate\Queue\QueueServiceProvider;
use Illuminate\Session\SessionServiceProvider;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;
use Roots\Acorn\Application;
use Roots\Acorn\Providers\AcornServiceProvider;
use Roots\Acorn\ServiceProvider as AcornProviders;
use Roots\Acorn\Tests\Test\TestCase;

use function Roots\Acorn\Tests\temp;

uses(TestCase::class);

afterEach(function () {
    ServiceProvider::$publishes = [];
    ServiceProvider::$publishGroups = [];
});

function publishableConfigs(Application $app): array
{
    $method = new ReflectionMethod(AcornServiceProvider::class, 'filterPublishableConfigs');

    return $method->invoke(new AcornServiceProvider($app));
}

it('includes configs for loaded providers', function () {
    $app = new Application();
    $app->register(SessionServiceProvider::class);

    expect(publishableConfigs($app))->toContain('app', 'services', 'session');
});

it('includes configs for deferred providers that have not loaded yet', function () {
    $app = new Application();
    $app->addDeferredServices([
        'queue' => QueueServiceProvider::class,
        'queue.connection' => QueueServiceProvider::class,
        'hash' => HashServiceProvider::class,
    ]);

    expect(publishableConfigs($app))->toContain('queue', 'hashing');
});

it('excludes configs for providers that are not registered', function () {
    $app = new Application();

    expect(publishableConfigs($app))->not->toContain('auth', 'queue', 'hashing');
});

it('publishes configs for deferred providers when booting the default providers', function () {
    $this->stub('is_multisite', fn () => false);

    @mkdir(($bootstrap = temp('publishable-configs/bootstrap')) . '/cache', 0o777, true);

    $app = new Application(temp('publishable-configs'));
    $app->useBootstrapPath($bootstrap);
    $app['env'] = 'production';
    $app->instance('config', new Repository([
        'app' => ['providers' => AcornProviders::defaultProviders()->toArray()],
    ]));
    $app->instance(PackageManifest::class, Mockery::mock(PackageManifest::class, ['providers' => []]));
    $app->instance(ExceptionHandler::class, Mockery::mock(ExceptionHandler::class)->shouldIgnoreMissing());

    Facade::setFacadeApplication($app);

    $app->registerConfiguredProviders();
    $app->boot();

    $published = array_map('basename', array_values(ServiceProvider::pathsToPublish(null, 'acorn-configs')));

    expect($published)->toContain('cache.php', 'hashing.php', 'queue.php');
});
