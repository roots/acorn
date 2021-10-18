<?php

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Roots\Acorn\Application;
use Roots\Acorn\Bootloader;
use Roots\Acorn\Tests\Test\TestCase;

use function Roots\Acorn\Tests\mock;
use function Roots\Acorn\Tests\temp;

uses(TestCase::class);

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
    $this->stub('locate_template')
        ->should()
        ->andReturn(temp(''));

    $application->shouldReceive('usePaths', 'bootstrapWith');
    $application->shouldReceive('setBasePath')
        ->once()
        ->with(\Mockery::not(temp('')));

    $bootloader();
});

it('should locate the `config` folder in the theme to determine basepath', function () {
    /** @var \Mockery\MockInterface|Application */
    $application = mock(Application::class);
    Application::setInstance($application);
    $bootloader = new Bootloader('hook', Application::class);
    $this->filter('acorn/ready', '__return_true');
    $this->filter('acorn/bootstrap', '__return_empty_array');

    $this->stub('locate_template', fn ($path) => temp($path))
        ->should()
        ->andReturn(temp('config'));

    $application->shouldReceive('usePaths', 'bootstrapWith');
    $application->shouldReceive('setBasePath')->once()->with(temp(''));

    $bootloader();
});

it('should locate other paths via filter', function () {
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

    $application->shouldReceive('bootstrapWith', 'setBasePath');
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
})->skip(!defined('ACORN_BASEPATH'), "This test is skipped by default. `define('ACORN_BASEPATH')` to run it.");
