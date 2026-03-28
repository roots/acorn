<?php

namespace Roots\Acorn\Console\Concerns;

use Illuminate\Contracts\Foundation\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

trait GracefullyCallsCommands
{
    /**
     * Get the Laravel application instance.
     *
     * @return Application
     */
    abstract public function getLaravel();

    /**
     * Resolve the console command instance for the given command.
     *
     * @param  Command|string  $command
     * @return Command
     */
    abstract protected function resolveCommand($command);

    /**
     * Run the given the console command.
     *
     * @param  Command|string  $command
     * @return int
     */
    abstract protected function runCommand($command, array $arguments, OutputInterface $output);

    /**
     * Call another console command.
     *
     * Silently fail if command does not exist.
     *
     * @param  Command|string  $command
     * @return int
     */
    public function gracefulCall($command, array $arguments = [])
    {
        if ($this->commandExists($command)) {
            return $this->runCommand($command, $arguments, $this->output);
        }

        return 0;
    }

    /**
     * Call another console command without output.
     *
     * Silently fail if command does not exist.
     *
     * @param  Command|string  $command
     * @return int
     */
    public function gracefulCallSilent($command, array $arguments = [])
    {
        if ($this->commandExists($command)) {
            return $this->runCommand($command, $arguments, new NullOutput());
        }

        return 0;
    }

    /**
     * Check whether a command exists.
     *
     * @param  Command|string  $command
     * @return bool
     */
    protected function commandExists($command)
    {
        try {
            $this->resolveCommand($command);
        } catch (CommandNotFoundException $e) {
            $this
                ->getLaravel()
                ->make('log')
                ->debug("Command [{$command}] not found. Skipping.");

            return false;
        }

        return true;
    }
}
