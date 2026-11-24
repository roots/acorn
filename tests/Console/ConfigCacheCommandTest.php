<?php

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Roots\Acorn\Application;
use Roots\Acorn\Console\Commands\ConfigCacheCommand;
use Roots\Acorn\Tests\Test\TestCase;

use function Roots\Acorn\Tests\temp;

uses(TestCase::class);

afterEach(function () {
    Container::setInstance(null);
    Facade::clearResolvedInstances();
    Facade::setFacadeApplication(null);
});

it('restores the application after reading the fresh configuration', function () {
    $app = new Application(temp('config_cache'));
    Facade::setFacadeApplication($app);

    $command = new class extends ConfigCacheCommand {
        public function __construct()
        {
            //
        }

        public function fresh()
        {
            return $this->getFreshConfiguration();
        }

        protected function getPristineConfiguration()
        {
            Facade::setFacadeApplication(new Application(temp('config_cache_fresh')));

            return ['app' => ['providers' => []]];
        }
    };

    $command->setLaravel($app);
    $command->fresh();

    expect(Container::getInstance())->toBe($app)->and(Facade::getFacadeApplication())->toBe($app);
});
