<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Container\Container;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Roots\Acorn\Console\Concerns\GetsFreshApplication;

class RouteCacheCommand extends \Illuminate\Foundation\Console\RouteCacheCommand
{
    use GetsFreshApplication {
        getFreshApplication as protected parentGetFreshApplication;
    }

    /**
     * Get a fresh application instance.
     *
     * @return Application
     */
    protected function getFreshApplication()
    {
        return tap($this->parentGetFreshApplication(), function ($app) {
            $app->make(ConsoleKernelContract::class)->bootstrap();

            Facade::clearResolvedInstances();

            Facade::setFacadeApplication($this->laravel);
            Container::setInstance($this->laravel);
        });
    }
}
