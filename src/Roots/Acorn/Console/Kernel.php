<?php

namespace Roots\Acorn\Console;

use Illuminate\Foundation\Console\ClearCompiledCommand;
use Illuminate\Foundation\Console\ComponentMakeCommand;
use Illuminate\Foundation\Console\ConfigClearCommand;
use Illuminate\Foundation\Console\ConsoleMakeCommand;
use Illuminate\Foundation\Console\EnvironmentCommand;
use Illuminate\Foundation\Console\Kernel as FoundationConsoleKernel;
use Illuminate\Foundation\Console\PackageDiscoverCommand;
use Illuminate\Foundation\Console\ProviderMakeCommand;
use Illuminate\Foundation\Console\ViewCacheCommand;
use Illuminate\Foundation\Console\ViewClearCommand;
use Roots\Acorn\Application;
use Roots\Acorn\Console\Commands\AcornInitCommand;
use Roots\Acorn\Console\Commands\ComposerMakeCommand;
use Roots\Acorn\Console\Commands\ConfigCacheCommand;
use Roots\Acorn\Console\Commands\OptimizeClearCommand;
use Roots\Acorn\Console\Commands\OptimizeCommand;
use Roots\Acorn\Console\Commands\SummaryCommand;
use Roots\Acorn\Console\Commands\VendorPublishCommand;

class Kernel extends FoundationConsoleKernel
{
    /**
     * The application implementation.
     *
     * @var Application
     */
    protected $app;

    /**
     * The Console commands provided by the application.
     *
     * @var array
     */
    protected $commands = [
        AcornInitCommand::class,
        ClearCompiledCommand::class,
        ComponentMakeCommand::class,
        ComposerMakeCommand::class,
        ConfigCacheCommand::class,
        ConfigClearCommand::class,
        ConsoleMakeCommand::class,
        EnvironmentCommand::class,
        OptimizeClearCommand::class,
        OptimizeCommand::class,
        PackageDiscoverCommand::class,
        ProviderMakeCommand::class,
        SummaryCommand::class,
        VendorPublishCommand::class,
        ViewCacheCommand::class,
        ViewClearCommand::class,
    ];

    /**
     * The bootstrap classes for the application.
     *
     * @var array
     */
    protected $bootstrappers = [];

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
