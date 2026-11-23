<?php

use Illuminate\Config\Repository;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Roots\Acorn\Application;
use Roots\Acorn\Tests\Test\TestCase;
use Roots\Acorn\View\ViewServiceProvider;

use function Roots\Acorn\Tests\temp;

uses(TestCase::class);

beforeEach(function () {
    // `temp()` creates the directory it returns, so the compiled path goes one level below it.
    $this->compiled = temp('make_loader/' . uniqid()) . '/views';

    $this->files = Mockery::mock(Filesystem::class)->makePartial();

    $this->app = new Application(temp('make_loader'));
    $this->app->instance('config', new Repository([
        'view' => [
            'paths' => [__DIR__ . '/__fixtures__/views'],
            'compiled' => $this->compiled,
            'namespaces' => [],
        ],
    ]));
    $this->app->instance('events', new Dispatcher($this->app));
    $this->app->instance('files', $this->files);
    $this->app->register(ViewServiceProvider::class);
});

it('creates the compiled directory and writes the loader', function () {
    expect(is_dir($this->compiled))->toBeFalse();

    $loader = $this->app['view']->make('greeting')->makeLoader();

    expect($loader)
        ->toStartWith($this->compiled . '/')
        ->toEndWith('-loader.php')
        ->toBeFile()
        ->and(file_get_contents($loader))
        ->toContain("\\Roots\\view('greeting', \$data ?? get_defined_vars())->render()");
});

it('writes the loader atomically', function () {
    $loader = $this->app['view']->make('greeting')->makeLoader();

    $this->files->shouldHaveReceived('replace')->once()->with($loader, Mockery::type('string'));
});

it('reuses an existing loader', function () {
    $view = $this->app['view']->make('greeting');

    $loader = $view->makeLoader();
    file_put_contents($loader, 'existing');

    expect($view->makeLoader())->toBe($loader)->and(file_get_contents($loader))->toBe('existing');
});

it('returns the loader path without writing when the compiled directory is not writable', function () {
    if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
        $this->markTestSkipped('File permissions are not enforced for root.');
    }

    mkdir($this->compiled, 0o555, true);

    $tempFiles = glob(sys_get_temp_dir() . '/*-loader.php*') ?: [];

    try {
        $loader = $this->app['view']->make('greeting')->makeLoader();
    } finally {
        chmod($this->compiled, 0o755);
    }

    expect($loader)
        ->toStartWith($this->compiled . '/')
        ->and(file_exists($loader))
        ->toBeFalse()
        ->and(glob(sys_get_temp_dir() . '/*-loader.php*') ?: [])
        ->toBe($tempFiles);
});
