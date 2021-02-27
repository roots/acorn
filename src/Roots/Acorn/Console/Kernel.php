<?php

namespace Roots\Acorn\Console;

use ReflectionClass;
use Illuminate\Console\Application as Console;
use Illuminate\Foundation\Console\ClearCompiledCommand;
use Illuminate\Foundation\Console\ComponentMakeCommand;
use Illuminate\Foundation\Console\ConfigClearCommand;
use Illuminate\Foundation\Console\ConsoleMakeCommand;
use Illuminate\Foundation\Console\EnvironmentCommand;
use Illuminate\Foundation\Console\Kernel as FoundationConsoleKernel;
use Illuminate\Foundation\Console\PackageDiscoverCommand;
use Illuminate\Foundation\Console\ProviderMakeCommand;
use Illuminate\Foundation\Console\VendorPublishCommand;
use Illuminate\Foundation\Console\ViewCacheCommand;
use Illuminate\Foundation\Console\ViewClearCommand;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Roots\Acorn\Application;
use Roots\Acorn\Console\Commands\Command;
use Roots\Acorn\Console\Commands\ComposerMakeCommand;
use Roots\Acorn\Console\Commands\ConfigCacheCommand;
use Roots\Acorn\Console\Commands\OptimizeClearCommand;
use Roots\Acorn\Console\Commands\OptimizeCommand;
use Roots\Acorn\Console\Commands\SummaryCommand;
use Symfony\Component\Finder\Finder;

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

    /**
     * Register all of the commands in the given directory.
     *
     * @param  array|string  $paths
     * @return void
     */
    protected function load($paths)
    {
        $paths = array_unique(Arr::wrap($paths));

        $paths = array_filter($paths, function ($path) {
            return is_dir($path);
        });

        if (empty($paths)) {
            return;
        }

        $namespace = $this->app->getNamespace();

        foreach ((new Finder())->in($paths)->files() as $command) {
            $command = $namespace . str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after($command->getPathname(), realpath($this->app->path()) . DIRECTORY_SEPARATOR)
            );

            if (
                is_subclass_of($command, Command::class) &&
                ! (new ReflectionClass($command))->isAbstract()
            ) {
                Console::starting(function ($artisan) use ($command) {
                    $artisan->resolve($command);
                });
            }
        }
    }
}
