<?php

namespace Roots\Acorn\Console;

use Illuminate\Cache\Console\ClearCommand;
use Illuminate\Cache\Console\ForgetCommand;
use Illuminate\Console\Scheduling\ScheduleClearCacheCommand;
use Illuminate\Console\Scheduling\ScheduleFinishCommand;
use Illuminate\Console\Scheduling\ScheduleInterruptCommand;
use Illuminate\Console\Scheduling\ScheduleListCommand;
use Illuminate\Console\Scheduling\ScheduleRunCommand;
use Illuminate\Console\Scheduling\ScheduleTestCommand;
use Illuminate\Console\Scheduling\ScheduleWorkCommand;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Console\DbCommand;
use Illuminate\Database\Console\Seeds\SeedCommand;
use Illuminate\Database\Console\Seeds\SeederMakeCommand;
use Illuminate\Database\Console\TableCommand;
use Illuminate\Database\Console\WipeCommand;
use Illuminate\Foundation\Bootstrap\BootProviders;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
use Illuminate\Foundation\Bootstrap\SetRequestForConsole;
use Illuminate\Foundation\Console\ClearCompiledCommand;
use Illuminate\Foundation\Console\ComponentMakeCommand;
use Illuminate\Foundation\Console\ConfigClearCommand;
use Illuminate\Foundation\Console\ConsoleMakeCommand;
use Illuminate\Foundation\Console\EnvironmentCommand;
use Illuminate\Foundation\Console\JobMakeCommand;
use Illuminate\Foundation\Console\Kernel as FoundationConsoleKernel;
use Illuminate\Foundation\Console\ModelMakeCommand;
use Illuminate\Foundation\Console\PackageDiscoverCommand;
use Illuminate\Foundation\Console\ProviderMakeCommand;
use Illuminate\Foundation\Console\RouteClearCommand;
use Illuminate\Foundation\Console\RouteListCommand;
use Illuminate\Foundation\Console\ViewCacheCommand;
use Illuminate\Foundation\Console\ViewClearCommand;
use Illuminate\Queue\Console\BatchesTableCommand;
use Illuminate\Queue\Console\FailedTableCommand;
use Illuminate\Queue\Console\FlushFailedCommand;
use Illuminate\Queue\Console\ForgetFailedCommand;
use Illuminate\Queue\Console\ListenCommand;
use Illuminate\Queue\Console\ListFailedCommand;
use Illuminate\Queue\Console\MonitorCommand;
use Illuminate\Queue\Console\PauseCommand;
use Illuminate\Queue\Console\PruneBatchesCommand;
use Illuminate\Queue\Console\PruneFailedJobsCommand;
use Illuminate\Queue\Console\RestartCommand;
use Illuminate\Queue\Console\ResumeCommand;
use Illuminate\Queue\Console\RetryBatchCommand;
use Illuminate\Queue\Console\RetryCommand;
use Illuminate\Queue\Console\WorkCommand;
use Illuminate\Routing\Console\ControllerMakeCommand;
use Illuminate\Routing\Console\MiddlewareMakeCommand;
use Roots\Acorn\Bootstrap\HandleExceptions;
use Roots\Acorn\Bootstrap\LoadConfiguration;
use Roots\Acorn\Bootstrap\LoadEnvironmentVariables;
use Roots\Acorn\Bootstrap\RegisterFacades;
use Roots\Acorn\Console\Commands\AboutCommand;
use Roots\Acorn\Console\Commands\AcornInitCommand;
use Roots\Acorn\Console\Commands\AcornInstallCommand;
use Roots\Acorn\Console\Commands\ComposerMakeCommand;
use Roots\Acorn\Console\Commands\ConfigCacheCommand;
use Roots\Acorn\Console\Commands\KeyGenerateCommand;
use Roots\Acorn\Console\Commands\OptimizeClearCommand;
use Roots\Acorn\Console\Commands\OptimizeCommand;
use Roots\Acorn\Console\Commands\RouteCacheCommand;
use Roots\Acorn\Console\Commands\SummaryCommand;
use Roots\Acorn\Console\Commands\VendorPublishCommand;

class Kernel extends FoundationConsoleKernel
{
    /**
     * The Console commands provided by the application.
     *
     * @var array
     */
    protected $commands = [
        ClearCommand::class,
        ForgetCommand::class,
        DbCommand::class,
        SeedCommand::class,
        SeederMakeCommand::class,
        TableCommand::class,
        WipeCommand::class,
        ClearCompiledCommand::class,
        ComponentMakeCommand::class,
        ConfigClearCommand::class,
        ConsoleMakeCommand::class,
        EnvironmentCommand::class,
        JobMakeCommand::class,
        ModelMakeCommand::class,
        PackageDiscoverCommand::class,
        ProviderMakeCommand::class,
        RouteClearCommand::class,
        RouteListCommand::class,
        ViewCacheCommand::class,
        ViewClearCommand::class,
        BatchesTableCommand::class,
        \Illuminate\Queue\Console\ClearCommand::class,
        FailedTableCommand::class,
        FlushFailedCommand::class,
        ForgetFailedCommand::class,
        ListFailedCommand::class,
        MonitorCommand::class,
        PauseCommand::class,
        PruneBatchesCommand::class,
        PruneFailedJobsCommand::class,
        RestartCommand::class,
        ResumeCommand::class,
        RetryBatchCommand::class,
        RetryCommand::class,
        \Illuminate\Queue\Console\TableCommand::class,
        ListenCommand::class,
        WorkCommand::class,
        ScheduleClearCacheCommand::class,
        ScheduleFinishCommand::class,
        ScheduleListCommand::class,
        ScheduleRunCommand::class,
        ScheduleWorkCommand::class,
        ScheduleTestCommand::class,
        ScheduleInterruptCommand::class,
        ControllerMakeCommand::class,
        MiddlewareMakeCommand::class,
        AboutCommand::class,
        AcornInitCommand::class,
        AcornInstallCommand::class,
        ComposerMakeCommand::class,
        ConfigCacheCommand::class,
        KeyGenerateCommand::class,
        OptimizeClearCommand::class,
        OptimizeCommand::class,
        RouteCacheCommand::class,
        SummaryCommand::class,
        VendorPublishCommand::class,
    ];

    /**
     * The bootstrap classes for the application.
     *
     * @var string[]
     */
    protected $bootstrappers = [
        LoadEnvironmentVariables::class,
        LoadConfiguration::class,
        HandleExceptions::class,
        RegisterFacades::class,
        SetRequestForConsole::class,
        RegisterProviders::class,
        BootProviders::class,
    ];

    /**
     * Create a new console kernel instance.
     *
     * @return void
     */
    public function __construct(Application $app, Dispatcher $events)
    {
        if (! defined('ARTISAN_BINARY')) {
            define('ARTISAN_BINARY', dirname(__DIR__, 4) . '/bin/acorn');
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
