<?php

use Illuminate\Container\Container;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
use Illuminate\Support\Facades\Facade;
use Roots\Acorn\Application;
use Roots\Acorn\Console\Concerns\GetsFreshApplication;
use Roots\Acorn\Tests\Test\TestCase;

use function Roots\Acorn\Tests\temp;

uses(TestCase::class);

afterEach(function () {
    GetsFreshApplicationTestApp::$acornBooted = false;
    Container::setInstance(null);
    Facade::clearResolvedInstances();
    RegisterProviders::flushState();
});

it('does not boot the fresh application into the current runtime', function () {
    $app = new GetsFreshApplicationTestApp(temp('fresh_app'));

    $command = new class {
        use GetsFreshApplication;

        public $laravel;

        public function fresh()
        {
            return $this->getFreshApplication();
        }
    };

    $command->laravel = $app;

    // The global instance can be a previous throwaway app, so it shouldn't be used.
    Container::setInstance(new Container());

    $fresh = $command->fresh();

    expect($fresh)->not->toBe($app)->and(GetsFreshApplicationTestApp::$acornBooted)->toBeFalse();
});

class GetsFreshApplicationTestApp extends Application
{
    public static bool $acornBooted = false;

    public function bootAcorn(array $bootConfiguration = []): static
    {
        static::$acornBooted = true;

        return $this;
    }
}
