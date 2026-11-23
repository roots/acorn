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
    $this->resolveWithConstant = function (string $constant, array $env = [], ?string $storage = null) {
        $process = new Process(
            [
                PHP_BINARY,
                '-d',
                'display_errors=stderr',
                __DIR__ . '/__fixtures__/bootstrap_path_constant.php',
                $this->base,
                $constant,
                ...array_filter([$storage]),
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

it('derives the bootstrap path from a storage path passed to withPaths', function () {
    $storage = temp('paths_custom_storage/' . uniqid());

    $app = new Application($this->base);

    (new ApplicationBuilder($app))
        ->withPaths()
        ->withPaths(storage: $storage);

    expect($app->bootstrapPath())->toBe("{$storage}/framework")->and(is_dir("{$storage}/framework/cache"))->toBeTrue();
});

it('prefers an explicit bootstrap path over the storage passed to withPaths', function () {
    $storage = temp('paths_custom_storage/' . uniqid());

    $app = new Application($this->base);

    (new ApplicationBuilder($app))->withPaths(storage: $storage, bootstrap: '/explicit/bootstrap');

    expect($app->bootstrapPath())->toBe('/explicit/bootstrap')->and(is_dir("{$storage}/framework"))->toBeFalse();
});

it('prefers the ACORN_BOOTSTRAP_PATH environment variable over the storage passed to withPaths', function () {
    $_ENV['ACORN_BOOTSTRAP_PATH'] = $bootstrap = temp('paths_env_bootstrap/' . uniqid());

    $storage = temp('paths_custom_storage/' . uniqid());

    $app = new Application($this->base);

    (new ApplicationBuilder($app))->withPaths(storage: $storage);

    expect($app->bootstrapPath())
        ->toBe($bootstrap)
        ->and(is_dir("{$bootstrap}/cache"))
        ->toBeFalse()
        ->and(is_dir("{$storage}/framework"))
        ->toBeFalse();
});

it('prefers the ACORN_BOOTSTRAP_PATH constant over the storage passed to withPaths', function () {
    $storage = temp('paths_custom_storage/' . uniqid());

    expect(($this->resolveWithConstant)('/constant/bootstrap', storage: $storage))
        ->toBe('/constant/bootstrap')
        ->and(is_dir("{$storage}/framework"))
        ->toBeFalse();
});
