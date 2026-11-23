<?php

use Roots\Acorn\Application;
use Roots\Acorn\Configuration\ApplicationBuilder;
use Roots\Acorn\Tests\Test\TestCase;
use Symfony\Component\Process\Process;

use function Roots\Acorn\Tests\temp;

uses(TestCase::class);

beforeEach(function () {
    $this->stub('get_theme_file_path', fn ($path = '') => "/nonexistent-theme/{$path}");

    $this->base = temp('paths_base');

    if (! is_dir("{$this->base}/storage")) {
        mkdir("{$this->base}/storage", 0o777, true);
    }

    $this->defaultPaths = fn () => (new ApplicationBuilder(new Application($this->base)))->defaultPaths();

    // Constants can't be undefined between tests, so resolve them in a separate process.
    $this->resolveWithConstant = function (string $constant, array $env = []) {
        $process = new Process(
            [
                PHP_BINARY,
                '-d',
                'display_errors=stderr',
                __DIR__ . '/__fixtures__/bootstrap_path_constant.php',
                $this->base,
                $constant,
            ],
            env: $env + ['ACORN_BOOTSTRAP_PATH' => false, 'ACORN_DATABASE_PATH' => false],
        );

        return $process->mustRun()->getOutput();
    };
});

afterEach(function () {
    unset($_ENV['ACORN_BOOTSTRAP_PATH'], $_ENV['ACORN_DATABASE_PATH']);
});

it('defaults the bootstrap path to the storage framework directory', function () {
    expect(($this->defaultPaths)()['bootstrap'])->toBe("{$this->base}/storage/framework");
});

it('uses an absolute ACORN_BOOTSTRAP_PATH environment variable', function () {
    $_ENV['ACORN_BOOTSTRAP_PATH'] = '/custom/bootstrap';

    expect(($this->defaultPaths)()['bootstrap'])->toBe('/custom/bootstrap');
});

it('resolves a relative ACORN_BOOTSTRAP_PATH environment variable from the base path', function () {
    $_ENV['ACORN_BOOTSTRAP_PATH'] = 'custom/bootstrap';

    expect(($this->defaultPaths)()['bootstrap'])->toBe("{$this->base}/custom/bootstrap");
});

it('does not use ACORN_DATABASE_PATH for the bootstrap path', function () {
    $_ENV['ACORN_DATABASE_PATH'] = '/custom/database';

    $paths = ($this->defaultPaths)();

    expect($paths['database'])
        ->toBe('/custom/database')
        ->and($paths['bootstrap'])
        ->toBe("{$this->base}/storage/framework");
});

it('uses the ACORN_BOOTSTRAP_PATH constant', function () {
    expect(($this->resolveWithConstant)('/constant/bootstrap'))->toBe('/constant/bootstrap');
});

it('prefers the ACORN_BOOTSTRAP_PATH environment variable over the constant', function () {
    expect(($this->resolveWithConstant)('/constant/bootstrap', ['ACORN_BOOTSTRAP_PATH' => '/env/bootstrap']))
        ->toBe('/env/bootstrap');
});

it('applies ACORN_BOOTSTRAP_PATH to the application', function () {
    $_ENV['ACORN_BOOTSTRAP_PATH'] = '/custom/bootstrap';

    $app = new Application($this->base);

    (new ApplicationBuilder($app))->withPaths();

    expect($app->bootstrapPath())->toBe('/custom/bootstrap');
});
