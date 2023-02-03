<?php

namespace Roots\Acorn\Console;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Console\Kernel as FoundationConsoleKernel;

class Kernel extends FoundationConsoleKernel
{
    /**
     * The Console commands provided by the application.
     *
     * @var array
     */
    protected $commands = [
        \Illuminate\Cache\Console\ClearCommand::class,
        \Illuminate\Cache\Console\ForgetCommand::class,
        \Illuminate\Foundation\Console\ClearCompiledCommand::class,
        \Illuminate\Foundation\Console\ComponentMakeCommand::class,
        \Illuminate\Foundation\Console\ConfigClearCommand::class,
        \Illuminate\Foundation\Console\ConsoleMakeCommand::class,
        \Illuminate\Foundation\Console\EnvironmentCommand::class,
        \Illuminate\Foundation\Console\PackageDiscoverCommand::class,
        \Illuminate\Foundation\Console\ProviderMakeCommand::class,
        \Illuminate\Foundation\Console\ViewCacheCommand::class,
        \Illuminate\Foundation\Console\ViewClearCommand::class,
        \Roots\Acorn\Console\Commands\AcornInitCommand::class,
        \Roots\Acorn\Console\Commands\ComposerMakeCommand::class,
        \Roots\Acorn\Console\Commands\ConfigCacheCommand::class,
        \Roots\Acorn\Console\Commands\OptimizeClearCommand::class,
        \Roots\Acorn\Console\Commands\OptimizeCommand::class,
        \Roots\Acorn\Console\Commands\SummaryCommand::class,
        \Roots\Acorn\Console\Commands\VendorPublishCommand::class,
    ];

    /**
     * The bootstrap classes for the application.
     *
     * @var string[]
     */
    protected $bootstrappers = [
        \Roots\Acorn\Bootstrap\SageFeatures::class,
        \Roots\Acorn\Bootstrap\LoadConfiguration::class,
        \Roots\Acorn\Bootstrap\HandleExceptions::class,
        \Roots\Acorn\Bootstrap\RegisterFacades::class,
        \Illuminate\Foundation\Bootstrap\SetRequestForConsole::class,
        \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
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
