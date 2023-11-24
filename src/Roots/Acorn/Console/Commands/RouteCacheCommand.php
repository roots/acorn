<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Filesystem\Filesystem;
use Roots\Acorn\Application;
use Symfony\Component\Process\Exception\ProcessSignaledException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;
use Roots\Acorn\Console\Concerns\GetsFreshApplication;
use Roots\Acorn\Console\Console;

class RouteCacheCommand extends \Illuminate\Foundation\Console\RouteCacheCommand
{
    use GetsFreshApplication {
        getFreshApplication as protected parentGetFreshApplication;
    }

    protected $console;

    public function __construct(Filesystem $files, Console $console)
    {
        parent::__construct($files);

        $this->console = $console;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (! Application::isExperimentalRouterEnabled()) {
            return;
        }

        if (! $this->ensureDependenciesExist()) {
            return;
        }

        parent::handle();
    }

    /**
     * Ensure the dependencies for the database commands are available.
     *
     * @return bool
     */
    protected function ensureDependenciesExist()
    {
        if (class_exists(\Laravel\SerializableClosure\SerializableClosure::class)) {
            return true;
        }

        $message = 'Route caching requires Serializable Closure (laravel/serializable-closure) package.';

        if ($this->components->confirm("{$message} Would you like to install it?")) {
            $this->installDependencies();
            if ($this->console->acorn('route:cache') === 0) {
                $this->components->info('Routes cached successfully.');
            }
        } else {
            $this->components->error($message);
        }

        return false;
    }

    /**
     * Install the command's dependencies.
     *
     * @return void
     *
     * @throws \Symfony\Component\Process\Exception\ProcessSignaledException
     *
     * @copyright Taylor Otwell
     * @link https://github.com/laravel/framework/blob/9.x/src/Illuminate/Database/Console/DatabaseInspectionCommand.php
     */
    protected function installDependencies()
    {
        $command = collect($this->console->findComposer())
            ->push('require laravel/serializable-closure')
            ->implode(' ');

        $process = Process::fromShellCommandline($command, null, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $this->components->warn($e->getMessage());
            }
        }

        try {
            $process->run(fn ($type, $line) => $this->output->write($line));
        } catch (ProcessSignaledException $e) {
            if (extension_loaded('pcntl') && $e->getSignal() !== SIGINT) {
                throw $e;
            }
        }
    }

    /**
     * Get a fresh application instance.
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    protected function getFreshApplication()
    {
        return tap($this->parentGetFreshApplication(), function ($app) {
            $app->make(ConsoleKernelContract::class)->bootstrap();
        });
    }
}
