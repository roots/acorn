<?php

use Illuminate\Config\Repository as ConfigRepository;
use Roots\Acorn\Application;
use Roots\Acorn\Tests\Test\Stubs\BootableServiceProvider;
use Roots\Acorn\Tests\Test\TestCase;

use function Roots\Acorn\Tests\mock;
use function Roots\Acorn\Tests\temp;

uses(TestCase::class);

it('instantiates with custom paths', function () {
    $app = new Application(null, [
        'app' => $this->fixture('use_paths/app'),
        'config' => $this->fixture('use_paths/config'),
    ]);

    expect($app['path'])->toBe($this->fixture('use_paths/app'));
    expect($app['path.config'])->toBe($this->fixture('use_paths/config'));
});

it('rejects invalid custom path types', function () {
    $app = new Application();

    $app->usePaths([
        'app' => $this->fixture('use_paths/app'),
        'not_a_valid_path_type' => $this->fixture('use_paths/resources/lang'),
    ]);
})->throws(Exception::class);

it('accepts an array of custom paths', function () {
    $app = new Application(temp('base_path'));

    expect($app['path'])->not->toBe($this->fixture('use_paths/app'));
    expect($app['path.lang'])->not->toBe($this->fixture('use_paths/resources/lang'));
    expect($app['path.config'])->not->toBe($this->fixture('use_paths/config'));
    expect($app['path.public'])->not->toBe($this->fixture('use_paths/public'));
    expect($app['path.storage'])->not->toBe($this->fixture('use_paths/storage'));
    expect($app['path.database'])->not->toBe($this->fixture('use_paths/database'));
    expect($app['path.resources'])->not->toBe($this->fixture('use_paths/resources'));
    expect($app['path.bootstrap'])->not->toBe($this->fixture('use_paths/bootstrap'));

    $app->usePaths([
        'app' => $this->fixture('use_paths/app'),
        'lang' => $this->fixture('use_paths/resources/lang'),
        'config' => $this->fixture('use_paths/config'),
        'public' => $this->fixture('use_paths/public'),
        'storage' => $this->fixture('use_paths/storage'),
        'database' => $this->fixture('use_paths/database'),
        'resources' => $this->fixture('use_paths/resources'),
        'bootstrap' => $this->fixture('use_paths/bootstrap'),
    ]);

    expect($app['path'])->toBe($this->fixture('use_paths/app'));
    expect($app['path.lang'])->toBe($this->fixture('use_paths/resources/lang'));
    expect($app['path.config'])->toBe($this->fixture('use_paths/config'));
    expect($app['path.public'])->toBe($this->fixture('use_paths/public'));
    expect($app['path.storage'])->toBe($this->fixture('use_paths/storage'));
    expect($app['path.database'])->toBe($this->fixture('use_paths/database'));
    expect($app['path.resources'])->toBe($this->fixture('use_paths/resources'));
    expect($app['path.bootstrap'])->toBe($this->fixture('use_paths/bootstrap'));
});

it('allows specific paths to be changed', function () {
    $app = new Application(temp('not_a_path'));

    expect($app['path.bootstrap'])->not->toBe($this->fixture('use_paths/bootstrap'));
    $app->useBootstrapPath($this->fixture('use_paths/bootstrap'));
    expect($app['path.bootstrap'])->toBe($this->fixture('use_paths/bootstrap'));

    expect($app['path.config'])->not->toBe($this->fixture('use_paths/config'));
    $app->useConfigPath($this->fixture('use_paths/config'));
    expect($app['path.config'])->toBe($this->fixture('use_paths/config'));

    expect($app['path.public'])->not->toBe($this->fixture('use_paths/public'));
    $app->usePublicPath($this->fixture('use_paths/public'));
    expect($app['path.public'])->toBe($this->fixture('use_paths/public'));

    expect($app['path.resources'])->not->toBe($this->fixture('use_paths/resource'));
    $app->useResourcePath($this->fixture('use_paths/resource'));
    expect($app['path.resources'])->toBe($this->fixture('use_paths/resource'));
});

it('goes down for maintenance when acorn maintenance file exists', function () {
    $app = new Application();

    expect($app->isDownForMaintenance())->toBeFalse();

    $app->useStoragePath($this->fixture('is_down_for_maintenance/storage'));

    expect($app->isDownForMaintenance())->toBeTrue();
});

it('goes down for maintenance when wordpress maintenance file exists', function () {
    $app = new Application();

    expect($app->isDownForMaintenance())->toBeFalse();

    touch(temp(('wp/.maintenance')));

    expect($app->isDownForMaintenance())->toBeTrue();
});

it('throws an exception if app namespace cannot be determined', function () {
    (new Application($this->fixture('get_namespace/a_bedrock_site/a_random_library')))->getNamespace();
})->throws(RuntimeException::class);

it('determines namespace based on app composer.json', function () {
    $app = new Application($this->fixture('get_namespace/a_sage_theme'));

    expect($app->getNamespace())->toBe('Sage\\');
});

it('determines namespace based on app ancestor composer.json', function () {
    $app = new Application($this->fixture('get_namespace/a_bedrock_site/a_sage_theme'));

    expect($app->getNamespace())->toBe('Bedrock\\Sage\\');
});

it('allows the app namespace to changed arbitrarily', function () {
    $app = new Application($this->fixture('get_namespace/a_sage_theme'));

    expect($app->getNamespace())->not->toBe('App\\');

    $app->useNamespace('App');

    expect($app->getNamespace())->toBe('App\\');
});

it('makes a thing', function () {
    $app = new Application();

    $app->bind('config', fn () => new ConfigRepository());

    expect($app->make('config'))->toBeInstanceOf(ConfigRepository::class);
});

it('boots a provider', function () {
    $provider = mock(BootableServiceProvider::class)->makePartial();
    $app = new Application();

    $provider->shouldReceive('register', 'boot')->once();

    $app->register($provider);

    $app->boot();
});

it('gracefully skips a provider that fails to boot', function () {
    $handler = mock(\Illuminate\Contracts\Debug\ExceptionHandler::class);
    $manifest = mock(\Roots\Acorn\PackageManifest::class);
    $app = new Application();

    $app['env'] = 'not-local-dev';

    $app->singleton(\Illuminate\Contracts\Debug\ExceptionHandler::class, fn () => $handler);
    $app->singleton(\Illuminate\Foundation\PackageManifest::class, fn () => $manifest);

    // the core of this test is to make sure that when a class or function is called that
    // does not exist, things don't blow up.
    $provider = new class($app) extends BootableServiceProvider {
        public function boot()
        {
            new \kjo();
        }
    };

    $handler
        ->shouldReceive('report')
        ->withArgs(fn (\Roots\Acorn\Exceptions\SkipProviderException $e) => expect($e->getMessage())->toContain('Skipping provider') || true)
        ->once();

    $manifest
        ->shouldReceive('getPackage')
        ->andReturn(get_class($provider));

    $app->register($provider);

    $app->boot();
});

it('gracefully skips a provider that does not exist', function () {
    $handler = mock(\Illuminate\Contracts\Debug\ExceptionHandler::class);
    $manifest = mock(\Roots\Acorn\PackageManifest::class);
    $app = new Application();

    $app['env'] = 'not-local-dev';

    $app->singleton(\Illuminate\Contracts\Debug\ExceptionHandler::class, fn () => $handler);
    $app->singleton(\Illuminate\Foundation\PackageManifest::class, fn () => $manifest);

    $handler
        ->shouldReceive('report')
        ->withArgs(fn (\Roots\Acorn\Exceptions\SkipProviderException $e) => expect($e->getMessage())->toContain('Skipping provider') || true)
        ->once();

    $manifest
        ->shouldReceive('getPackage')
        ->andReturn(ThisProviderDoesNotExist::class);

    $app->register(ThisProviderDoesNotExist::class);

    $app->boot();
});

it('uses custom aliases', function () {
    $app = new Application();

    expect($app->getAlias(\Roots\Acorn\Application::class))->toBe('app');
    expect($app->getAlias(\Roots\Acorn\PackageManifest::class))->toBe(\Illuminate\Foundation\PackageManifest::class);
});
