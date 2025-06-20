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
        \Illuminate\Database\Console\DbCommand::class,
        \Illuminate\Database\Console\Seeds\SeedCommand::class,
        \Illuminate\Database\Console\Seeds\SeederMakeCommand::class,
        \Illuminate\Database\Console\TableCommand::class,
        \Illuminate\Database\Console\WipeCommand::class,
        \Illuminate\Foundation\Console\ClearCompiledCommand::class,
        \Illuminate\Foundation\Console\ComponentMakeCommand::class,
        \Illuminate\Foundation\Console\ConfigClearCommand::class,
        \Illuminate\Foundation\Console\ConsoleMakeCommand::class,
        \Illuminate\Foundation\Console\EnvironmentCommand::class,
        \Illuminate\Foundation\Console\JobMakeCommand::class,
        \Illuminate\Foundation\Console\PackageDiscoverCommand::class,
        \Illuminate\Foundation\Console\ProviderMakeCommand::class,
        \Illuminate\Foundation\Console\RouteClearCommand::class,
        \Illuminate\Foundation\Console\RouteListCommand::class,
        \Illuminate\Foundation\Console\ViewCacheCommand::class,
        \Illuminate\Foundation\Console\ViewClearCommand::class,
        \Illuminate\Queue\Console\BatchesTableCommand::class,
        \Illuminate\Queue\Console\FailedTableCommand::class,
        \Illuminate\Queue\Console\TableCommand::class,
        \Illuminate\Queue\Console\WorkCommand::class,
        \Illuminate\Queue\Console\ClearCommand::class,
        \Illuminate\Console\Scheduling\ScheduleListCommand::class,
        \Illuminate\Console\Scheduling\ScheduleRunCommand::class,
        \Illuminate\Console\Scheduling\ScheduleWorkCommand::class,
        \Illuminate\Console\Scheduling\ScheduleTestCommand::class,
        \Illuminate\Console\Scheduling\ScheduleInterruptCommand::class,
        \Illuminate\Routing\Console\ControllerMakeCommand::class,
        \Illuminate\Routing\Console\MiddlewareMakeCommand::class,
        \Roots\Acorn\Console\Commands\AboutCommand::class,
        \Roots\Acorn\Console\Commands\AcornInitCommand::class,
        \Roots\Acorn\Console\Commands\AcornInstallCommand::class,
        \Roots\Acorn\Console\Commands\ComposerMakeCommand::class,
        \Roots\Acorn\Console\Commands\ConfigCacheCommand::class,
        \Roots\Acorn\Console\Commands\KeyGenerateCommand::class,
        \Roots\Acorn\Console\Commands\OptimizeClearCommand::class,
        \Roots\Acorn\Console\Commands\OptimizeCommand::class,
        \Roots\Acorn\Console\Commands\RouteCacheCommand::class,
        \Roots\Acorn\Console\Commands\SummaryCommand::class,
        \Roots\Acorn\Console\Commands\VendorPublishCommand::class,
    ];

    /**
     * The bootstrap classes for the application.
     *
     * @var string[]
     */
    protected $bootstrappers = [
        \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
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
     * @return void
     */
    public function __construct(Application $app, Dispatcher $events)
    {
        if (! defined('ARTISAN_BINARY')) {
            define('ARTISAN_BINARY', dirname(__DIR__, 4).'/bin/acorn');
        }

        parent::__construct($app, $events);
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    public function commands()
    {
        $this->load($this->app->path('Console/Commands'));

        if (file_exists($routes = base_path('routes/console.php'))) {
            require $routes;
        }
    }
}
