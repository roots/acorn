<?php

use Roots\Acorn\Configuration\ApplicationBuilder;
use Roots\Acorn\Tests\Test\TestCase;

use function Roots\Acorn\Tests\acorn_root;

uses(TestCase::class);

it('uses the APP_BASE_PATH environment variable if set', function () {
    $_ENV['APP_BASE_PATH'] = $path = $this->fixture('base_path/base_empty');
    $this->assertEquals($path, ApplicationBuilder::inferBasePath());
    unset($_ENV['APP_BASE_PATH']);
});

it('uses the ACORN_BASEPATH constant if set', function () {
    define('ACORN_BASEPATH', $path = $this->fixture('base_path/base_empty'));
    $this->assertEquals($path, ApplicationBuilder::inferBasePath());
})->skip('This test is skipped because it defines a constant');

it('uses the directory of the composer.json file in the theme as the base path', function () {
    $this->stub('get_theme_file_path', fn ($path) => $this->fixture("base_path/base_composer/{$path}"));
    $path = $this->fixture('base_path/base_composer');
    $this->assertEquals($path, ApplicationBuilder::inferBasePath());
});

it('uses the directory of the app path in the theme as the base path', function () {
    $this->stub('get_theme_file_path', fn ($path) => $this->fixture("base_path/base_app/{$path}"));
    $path = $this->fixture('base_path/base_app');
    $this->assertEquals($path, ApplicationBuilder::inferBasePath());
});

it('uses acorn directory as fallback base path', function () {
    $this->stub('get_theme_file_path', fn ($path) => $this->fixture("base_path/base_empty/{$path}"));
    $this->assertEquals(acorn_root(), ApplicationBuilder::inferBasePath());
});
