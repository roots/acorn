<?php

namespace Roots\Acorn\Console\Concerns;

use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

trait GracefullyCallsCommands
{
    /**
     * Get the Laravel application instance.
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    abstract public function getLaravel();

    /**
     * Resolve the console command instance for the given command.
     *
     * @param  \Symfony\Component\Console\Command\Command|string  $command
     * @return \Symfony\Component\Console\Command\Command
     */
    abstract protected function resolveCommand($command);

    /**
     * Run the given the console command.
     *
     * @param  \Symfony\Component\Console\Command\Command|string  $command
     * @param  array  $arguments
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return int
     */
    abstract protected function runCommand($command, array $arguments, OutputInterface $output);

    /**
     * Call another console command.
     *
     * Silently fail if command does not exist.
     *
     * @param  \Symfony\Component\Console\Command\Command|string  $command
     * @param  array  $arguments
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
     * @param  \Symfony\Component\Console\Command\Command|string  $command
     * @param  array  $arguments
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
     * @param \Symfony\Component\Console\Command\Command|string  $command
     * @return bool
     */
    protected function commandExists($command)
    {
        try {
            $this->resolveCommand($command);
        } catch (CommandNotFoundException $e) {
            $this->getLaravel()->make('log')->debug("Command [{$command}] not found. Skipping.");
            return false;
        }

        return true;
    }
}
