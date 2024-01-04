<?php

use Roots\Acorn\Bootloader;
use Roots\Acorn\Tests\Test\TestCase;

use function Roots\Acorn\Tests\mock;

uses(TestCase::class);

beforeEach(function () {
    unset($_ENV['APP_BASE_PATH']);
});

it('should get a new instance', function () {
    expect(Bootloader::getInstance())->toBeInstanceOf(Bootloader::class);
});

it('should reuse the same instance', function () {
    expect(Bootloader::getInstance())->toBe(Bootloader::getInstance());
});

it('should get a new application instance', function () {
    expect((new Bootloader)->getApplication())->toBeInstanceOf(\Illuminate\Contracts\Foundation\Application::class);
});

it('should set the basePath if env var is set', function () {
    $_ENV['APP_BASE_PATH'] = $path = $this->fixture('base_path/base_empty');

    $app = (new Bootloader)->getApplication();

    expect($app->basePath())->toBe($path);
});

it('should set the basePath if composer.json exists in theme', function () {
    $composerPath = $this->fixture('base_path/base_composer');

    $this->stub('get_theme_file_path', fn ($path) => "{$composerPath}/{$path}");

    $app = (new Bootloader)->getApplication();

    expect($app->basePath())->toBe($composerPath);
});

it('should set the basePath if app exists in theme', function () {
    $appPath = $this->fixture('base_path/base_app');
    $this->stub('get_theme_file_path', fn () => $appPath);

    $app = Bootloader::getInstance()->getApplication();

    expect($app->basePath())->toBe(dirname($appPath));
});

it('should set the basePath if composer.json exists as ancestor of ../../../', function () {
    $files = mock(\Roots\Acorn\Filesystem\Filesystem::class);
    $this->stub('get_theme_file_path', fn () => '');

    $composerPath = $this->fixture('base_path/base_composer');

    $files->shouldReceive('closest')->andReturn("{$composerPath}/composer.json");
    $files->shouldReceive('ensureDirectoryExists');

    $app = (new Bootloader(null, $files))->getApplication();

    expect($app->basePath())->toBe($composerPath);
});
