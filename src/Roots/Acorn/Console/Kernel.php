<?php

namespace Roots\Acorn\Console;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Console\Kernel as FoundationConsoleKernel;
use Roots\Acorn\Application;



class Kernel extends FoundationConsoleKernel
{
    /**
     * The bootstrap classes for the application.
     *
     * @var array
     */
    protected $bootstrappers = [
        \Roots\Acorn\Bootstrap\SageFeatures::class,
        \Roots\Acorn\Bootstrap\LoadConfiguration::class,
        \Roots\Acorn\Bootstrap\HandleExceptions::class,
        \Roots\Acorn\Bootstrap\RegisterFacades::class,
        \Illuminate\Foundation\Bootstrap\SetRequestForConsole::class,
        \Roots\Acorn\Bootstrap\RegisterProviders::class,
        \Illuminate\Foundation\Bootstrap\BootProviders::class,
    ];

    /**
     * Create a new console kernel instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function __construct(Application $app, Dispatcher $events)
    {
        if (! defined('ARTISAN_BINARY')) {
            define('ARTISAN_BINARY', dirname(__DIR__, 4) . '/bin/acorn');
        }

        $this->app = $app;
        $this->events = $events;

        $this->app->booted(function () {
            $this->defineConsoleSchedule();
        });
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    public function commands()
    {
        $this->load($this->app->path('Console/Commands'));
    }
}
