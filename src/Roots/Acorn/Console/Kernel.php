<?php

namespace Roots\Acorn\Console;

use Illuminate\Foundation\Console\Kernel as FoundationConsoleKernel;

class Kernel extends FoundationConsoleKernel
{
    /**
     * The Console commands provided by the application.
     *
     * @var array
     */
    protected $commands = [
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
        \Roots\Acorn\Console\VendorPublishCommand::class,
    ];

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
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    public function commands()
    {
        $this->load($this->app->path('Console/Commands'));
    }
}
