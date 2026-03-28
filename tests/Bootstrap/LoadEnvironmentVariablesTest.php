<?php

use Dotenv\Dotenv;
use Illuminate\Contracts\Foundation\Application;
use Roots\Acorn\Bootstrap\LoadEnvironmentVariables;
use Roots\Acorn\Tests\Test\TestCase;

uses(TestCase::class);

class TestableLoadEnvironmentVariables extends LoadEnvironmentVariables
{
    public bool $dotenvLoaded = false;

    protected function createDotenv($app)
    {
        $this->dotenvLoaded = true;

        $dotenv = Mockery::mock(Dotenv::class);
        $dotenv->shouldReceive('safeLoad')->once();

        return $dotenv;
    }
}

function createEnvAppMock(string $envPath, string $envFile = '.env', bool $cached = false): Application
{
    $app = Mockery::mock(Application::class);
    $app->shouldReceive('configurationIsCached')->andReturn($cached);
    $app->shouldReceive('environmentPath')->andReturn($envPath);
    $app->shouldReceive('environmentFile')->andReturn($envFile);
    $app->shouldReceive('runningInConsole')->andReturn(false);

    return $app;
}

it('should skip loading when env file does not exist', function () {
    $app = createEnvAppMock($this->fixture('env/without-file'));

    $loader = new TestableLoadEnvironmentVariables();
    $loader->bootstrap($app);

    expect($loader->dotenvLoaded)->toBeFalse();
});

it('should load env file when it exists', function () {
    $app = createEnvAppMock($this->fixture('env/with-file'));

    $loader = new TestableLoadEnvironmentVariables();
    $loader->bootstrap($app);

    expect($loader->dotenvLoaded)->toBeTrue();
});

it('should skip loading when configuration is cached', function () {
    $app = createEnvAppMock($this->fixture('env/with-file'), cached: true);

    $loader = new TestableLoadEnvironmentVariables();
    $loader->bootstrap($app);

    expect($loader->dotenvLoaded)->toBeFalse();
});
