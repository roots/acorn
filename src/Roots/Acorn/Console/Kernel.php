<?php

namespace Roots\Acorn\Console;

use Exception;
use Throwable;
use RuntimeException;
use ReflectionClass;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Application as Console;
use Illuminate\Contracts\Console\Kernel as KernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Roots\Acorn\Application;
use Roots\Acorn\Console\Commands\Command;
use Roots\Acorn\Console\Commands\ComponentMakeCommand;
use Roots\Acorn\Console\Commands\ComposerMakeCommand;
use Roots\Acorn\Console\Commands\ConfigCacheCommand;
use Roots\Acorn\Console\Commands\ConfigClearCommand;
use Roots\Acorn\Console\Commands\ConsoleMakeCommand;
use Roots\Acorn\Console\Commands\OptimizeClearCommand;
use Roots\Acorn\Console\Commands\OptimizeCommand;
use Roots\Acorn\Console\Commands\PackageClearCommand;
use Roots\Acorn\Console\Commands\PackageDiscoverCommand;
use Roots\Acorn\Console\Commands\ProviderMakeCommand;
use Roots\Acorn\Console\Commands\SummaryCommand;
use Roots\Acorn\Console\Commands\VendorPublishCommand;
use Roots\Acorn\Console\Commands\ViewCacheCommand;
use Roots\Acorn\Console\Commands\ViewClearCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class Kernel implements KernelContract
{
    /**
     * The application implementation.
     *
     * @var Application
     */
    protected $app;

    /**
     * The Console application instance.
     *
     * @var Console
     */
    protected $console;

    /**
     * Indicates if facade aliases are enabled for the console.
     *
     * @var bool
     */
    protected $aliases = true;

    /**
     * The Console commands provided by the application.
     *
     * @var array
     */
    protected $commands = [
        ComposerMakeCommand::class,
        ConfigCacheCommand::class,
        ConfigClearCommand::class,
        ConsoleMakeCommand::class,
        ComponentMakeCommand::class,
        OptimizeClearCommand::class,
        OptimizeCommand::class,
        PackageClearCommand::class,
        PackageDiscoverCommand::class,
        ProviderMakeCommand::class,
        SummaryCommand::class,
        VendorPublishCommand::class,
        ViewCacheCommand::class,
        ViewClearCommand::class,
    ];

    /**
     * Create a new console kernel instance.
     *
     * @param  Application  $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->app->booted(function () {
            $this->app->prepareForConsoleCommand($this->aliases);
            $this->defineConsoleSchedule();
        });
    }

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function defineConsoleSchedule()
    {
        $this->app->instance(Schedule::class, $schedule = new Schedule());

        $this->schedule($schedule);
    }

    /**
     * Run the console application.
     *
     * @param  InputInterface   $input
     * @param  OutputInterface $output
     * @return int
     */
    public function handle($input, $output = null)
    {
        try {
            return $this->getConsole()->run($input, $output);
        } catch (Exception $e) {
            $this->reportException($e);

            $this->renderException($output, $e);

            return 1;
        } catch (Throwable $e) {
            $this->reportException($e);

            $this->renderException($output, $e);

            return 1;
        }
    }

    /**
     * Terminate the application.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  int  $status
     * @return void
     */
    public function terminate($input, $status)
    {
        $this->app->terminate();
    }

    /**
     * Define the application's command schedule.
     *
     * @param  Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }

    /**
     * Run an Console console command by name.
     *
     * @param  string  $command
     * @param  array  $parameters
     * @return int
     */
    public function call($command, array $parameters = [], $outputBuffer = null)
    {
        return $this->getConsole()->call($command, $parameters, $outputBuffer);
    }

    /**
     * Queue the given console command.
     *
     * @param  string  $command
     * @param  array   $parameters
     * @return void
     */
    public function queue($command, array $parameters = [])
    {
        throw new RuntimeException('Queueing Console commands is not supported.');
    }

    /**
     * Get all of the commands registered with the console.
     *
     * @return array
     */
    public function all()
    {
        return $this->getConsole()->all();
    }

    /**
     * Get the output for the last run command.
     *
     * @return string
     */
    public function output()
    {
        return $this->getConsole()->output();
    }

    /**
     * Register application commands.
     *
     * @return string
     */
    public function commands()
    {
        $this->load($this->app->path('Console/Commands'));
    }

    /**
     * Get the Console application instance.
     *
     * @return Console
     */
    protected function getConsole()
    {
        if (is_null($this->console)) {
            return $this->console = (new Console($this->app, $this->app->make('events'), $this->app->version()))
                                ->resolveCommands($this->commands);
        }

        return $this->console;
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
                Console::starting(function ($console) use ($command) {
                    $console->resolve($command);
                });
            }
        }
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param  Throwable  $e
     * @return void
     */
    protected function reportException(Throwable $e)
    {
        $this->app[ExceptionHandler::class]->report($e);
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param  OutputInterface  $output
     * @param  Throwable  $e
     * @return void
     */
    protected function renderException($output, Throwable $e)
    {
        $this->app[ExceptionHandler::class]->renderForConsole($output, $e);
    }
}
