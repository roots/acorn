<?php

use Akamon\MockeryCallableMock\MockeryCallableMock;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Roots\Acorn\Application;
use Roots\Acorn\Bootloader;
use Roots\Acorn\ServiceProvider;
use Roots\Acorn\Tests\Test\TestCase;

use function Roots\Acorn\Tests\acorn_root;
use function Roots\Acorn\Tests\mock;
use function Roots\Acorn\Tests\temp;
use function Roots\bootloader;

uses(TestCase::class);

it('should get a new instance', function () {
    expect(Bootloader::getInstance())->toBeInstanceOf(Bootloader::class);
});

it('should add multiple WordPress hooks', function () {
    $this->stub('add_filter')
        ->shouldBeCalled()
        ->twice()
        ->with(new Bootloader(['hook_1', 'hook_2']));
});

it('should add a single WordPress hook', function () {
    $this->stub('add_filter')
        ->shouldBeCalled()
        ->once()
        ->with(new Bootloader('hook_1'));
});

it('should not add a hook if empty array is passed', function () {
    $stub = $this->stub('add_filter');

    new Bootloader([]);

    $stub->shouldNotHaveBeenCalled();
});

it('should fail if class does not implement Application interface', function () {
    expect(new Bootloader([], get_class(new class {})));
})->throws(InvalidArgumentException::class);

it('should not be ready by default', function () {
    expect((new Bootloader())->ready())->toBeFalse();
});

it('should be ready if any of its hooks were already executed', function () {
    $this->stub('did_action')->shouldBeCalled()->with('hook')->andReturn(true);

    expect((new Bootloader('hook'))->ready())->toBeTrue();
});

it('should be ready if any of its hooks are currently executing', function () {
    $this->stub('doing_action')->shouldBeCalled()->with('hook')->andReturn(true);

    expect((new Bootloader('hook'))->ready())->toBeTrue();
});

it('should be ready if the filter is truthy', function () {
    $this->filter('acorn/ready', '__return_true');

    expect((new Bootloader())->ready())->toBeTrue();
});

it('should not boot the application by default when invoked', function () {
    /** @var \Mockery\MockInterface|ApplicationContract */
    $application = mock(ApplicationContract::class);
    Application::setInstance($application);

    $bootloader = new Bootloader('hook', Application::class);

    $application->shouldNotReceive('setBasePath', 'usePaths', 'bootstrapWith');

    $bootloader();
});

it('should boot the application when invoked if ready', function () {
    /** @var \Mockery\MockInterface|ApplicationContract */
    $application = mock(ApplicationContract::class);
    Application::setInstance($application);

    $bootloader = new Bootloader('hook', Application::class);
    $this->stub('doing_action')->shouldBeCalled()->with('hook')->andReturn(true);

    $application->shouldReceive('setBasePath', 'usePaths', 'bootstrapWith');

    $bootloader();
});

it('should allow Bootstraps to be filtered', function () {
    /** @var \Mockery\MockInterface|ApplicationContract */
    $application = mock(ApplicationContract::class);
    Application::setInstance($application);
    $bootstraps = ['MyBootstrap'];
    $this->filter('acorn/bootstrap', fn () => $bootstraps);

    $bootloader = new Bootloader('hook', Application::class);
    $this->stub('doing_action')->shouldBeCalled()->with('hook')->andReturn(true);

    $application->shouldReceive('setBasePath', 'usePaths');
    $application->shouldReceive('bootstrapWith')->once()->with($bootstraps);

    $bootloader();
});

it('should use a fallback basepath', function () {
    /** @var \Mockery\MockInterface|Application */
    $application = mock(Application::class);
    Application::setInstance($application);
    $bootloader = new Bootloader('hook', Application::class);
    $this->filter('acorn/ready', '__return_true');
    $this->filter('acorn/bootstrap', '__return_empty_array');
    $this->stub('get_theme_file_path')
        ->should()
        ->andReturn(temp(''));

    $application->shouldReceive('usePaths', 'bootstrapWith');
    $application->shouldReceive('setBasePath')
        ->once()
        ->with(\Mockery::not(temp('')));

    $bootloader();
});

it('should locate `composer.json` in the theme to determine basepath', function () {
    /** @var \Mockery\MockInterface|Application */
    $application = mock(Application::class);
    Application::setInstance($application);
    $bootloader = new Bootloader('hook', Application::class);
    $this->filter('acorn/ready', '__return_true');
    $this->filter('acorn/bootstrap', '__return_empty_array');

    $this->stub('get_theme_file_path', fn ($path) => temp($path))
        ->should()
        ->andReturn(temp('composer.json'));

    $application->shouldReceive('usePaths', 'bootstrapWith');
    $application->shouldReceive('setBasePath')->once()->with(temp(''));

    $bootloader();
});

it('should support zero-config paths', function () {
    /** @var \Mockery\MockInterface|Application */
    $application = mock(Application::class);
    Application::setInstance($application);
    $bootloader = new Bootloader('hook', Application::class);
    $this->filter('acorn/ready', '__return_true');
    $this->filter('acorn/bootstrap', '__return_empty_array');
    $this->stub('get_theme_file_path')
        ->should()
        ->andReturn(temp('theme/composer.json'));

    $application->shouldReceive('bootstrapWith', 'setBasePath')->once();
    $application->shouldReceive('usePaths')->once()->with([
        'app' => temp('theme') . '/app',
        'config' => acorn_root('config'),
        'storage' => temp('app/cache/acorn'),
        'resources' => acorn_root('resources'),
        'bootstrap' => temp('app/cache/acorn') . '/framework',
        'public' => temp('theme') . '/public',
    ]);

    $bootloader();
});


it('should locate paths via filter', function () {
    /** @var \Mockery\MockInterface|Application */
    $application = mock(Application::class);
    Application::setInstance($application);
    $bootloader = new Bootloader('hook', Application::class);
    $this->filter('acorn/ready', '__return_true');
    $this->filter('acorn/bootstrap', '__return_empty_array');
    $this->filter('acorn/paths', fn () => [
        'app' => temp('app'),
        'config' => temp('config'),
        'storage' => temp('storage'),
        'resources' => temp('resources'),
        'public' => temp('public'),
    ]);

    $application->shouldReceive('bootstrapWith', 'setBasePath')->once();
    $application->shouldReceive('usePaths')->once()->with([
        'app' => temp('app'),
        'config' => temp('config'),
        'storage' => temp('storage'),
        'resources' => temp('resources'),
        'bootstrap' => temp('storage/framework'), // <- this is intentionally unavailable via acorn/paths
        'public' => temp('public'),
    ]);

    $bootloader();
});

it('should locate specific paths via filter', function () {
    /** @var \Mockery\MockInterface|Application */
    $application = mock(Application::class);
    Application::setInstance($application);
    $bootloader = new Bootloader('hook', Application::class);
    $this->filter('acorn/ready', '__return_true');
    $this->filter('acorn/bootstrap', '__return_empty_array');
    $this->filter('acorn/paths.app', fn () => temp('app'));
    $this->filter('acorn/paths.config', fn () => temp('config'));
    $this->filter('acorn/paths.storage', fn () => temp('storage'));
    $this->filter('acorn/paths.resources', fn () => temp('resources'));
    $this->filter('acorn/paths.bootstrap', fn () => temp('bootstrap'));
    $this->filter('acorn/paths.public', fn () => temp('public'));

    $application->shouldReceive('bootstrapWith', 'setBasePath')->once();
    $application->shouldReceive('usePaths')->once()->with([
        'app' => temp('app'),
        'config' => temp('config'),
        'storage' => temp('storage'),
        'resources' => temp('resources'),
        'bootstrap' => temp('bootstrap'),
        'public' => temp('public'),
    ]);

    $bootloader();
});

it('should override basepath with constant', function () {
    /** @var \Mockery\MockInterface|Application */
    $application = mock(Application::class);
    Application::setInstance($application);
    $bootloader = new Bootloader('hook', Application::class);
    $this->filter('acorn/ready', '__return_true');
    $this->filter('acorn/bootstrap', '__return_empty_array');
    $this->stub('locate_template')
        ->should()
        ->andReturn(temp(''));

    $application->shouldReceive('usePaths', 'bootstrapWith');
    $application->shouldReceive('setBasePath')
        ->once()
        ->with(temp('acorn_basepath'));

    $bootloader();
})->skip(!defined('ACORN_BASEPATH'), "This test is skipped by default. `define('ACORN_BASEPATH')` to run it.")->group('requires-isolation');

it('should immediately execute a callback if ready', function () {
    /** @var \Mockery\MockInterface|Application */
    $application = mock(Application::class)->makePartial();
    Application::setInstance($application);
    $bootloader = new Bootloader('hook', Application::class);
    $callback = new MockeryCallableMock();

    $this->filter('acorn/ready', '__return_true');
    $this->filter('acorn/bootstrap', '__return_empty_array');

    $application->shouldReceive('setBasePath', 'usePaths', 'bootstrapWith')->once();

    $bootloader();
    $bootloader->call($callback);

    $callback->shouldHaveBeenCalled()->once();
});

it('should defer execution of callback until ready', function () {
    /** @var \Mockery\MockInterface|Application */
    $application = mock(Application::class)->makePartial();
    Application::setInstance($application);
    $bootloader = new Bootloader('hook', Application::class);
    $callback = new MockeryCallableMock();

    $this->filter('acorn/bootstrap', '__return_empty_array');

    $application->shouldReceive('setBasePath', 'usePaths', 'bootstrapWith')->once();

    $bootloader->call($callback);
    $this->filter('acorn/ready', '__return_true');

    $application->shouldNotHaveBeenCalled();
    $callback->shouldNotHaveBeenCalled();

    $bootloader();

    $callback->shouldHaveBeenCalled()->once();
});

it('should register a provider after boot', function () {
    /** @var \Mockery\MockInterface|Application */
    $application = mock(Application::class)->makePartial();
    Application::setInstance($application);
    $bootloader = new Bootloader('hook', Application::class);
    $provider = mock(ServiceProvider::class);

    $this->filter('acorn/bootstrap', '__return_empty_array');

    $application->shouldReceive('setBasePath', 'usePaths', 'bootstrapWith', 'register')->once();
    $application->shouldReceive('make')->withArgs([ApplicationContract::class])->andReturn($application);

    $bootloader->register($provider);
    $this->filter('acorn/ready', '__return_true');
    $application->shouldNotHaveReceived('register');
    $bootloader();
});

it('should boot using the helper', function () {
    /** @var \Mockery\MockInterface|Application */
    $application = mock(Application::class);
    Application::setInstance($application);
    $bootloader = new Bootloader('hook', Application::class);
    Bootloader::setInstance($bootloader);

    $this->filter('acorn/ready', '__return_true');
    $this->filter('acorn/bootstrap', '__return_empty_array');

    $application->shouldReceive('setBasePath', 'usePaths', 'bootstrapWith')->once();

    bootloader();
    $application->shouldNotHaveBeenCalled();

    do_action('hook');
});

it('should not boot more than once when using the helper', function () {
    /** @var \Mockery\MockInterface|Application */
    $application = mock(Application::class);
    Application::setInstance($application);
    $bootloader = new Bootloader('hook', Application::class);
    Bootloader::setInstance($bootloader);

    $this->filter('acorn/ready', '__return_true');
    $this->filter('acorn/bootstrap', '__return_empty_array');

    $application->shouldReceive('setBasePath', 'usePaths', 'bootstrapWith')->once();

    bootloader();
    $application->shouldNotHaveBeenCalled();

    bootloader();
    do_action('hook');

    bootloader();
    do_action('hook');
});

it('should handle callback when passed to the helper', function () {
    /** @var \Mockery\MockInterface|Application */
    $application = mock(Application::class)->makePartial();
    Application::setInstance($application);
    $bootloader = new Bootloader('hook', Application::class);
    Bootloader::setInstance($bootloader);
    $callback = new MockeryCallableMock();

    $this->filter('acorn/bootstrap', '__return_empty_array');

    $application->shouldReceive('setBasePath', 'usePaths', 'bootstrapWith')->once();

    bootloader($callback);
    $application->shouldNotHaveBeenCalled();
    $callback->shouldNotHaveBeenCalled();

    $this->filter('acorn/ready', '__return_true');
    do_action('hook');

    $callback->shouldHaveBeenCalled()->once();
});
