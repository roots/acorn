<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Foundation\Console\OptimizeClearCommand as FoundationOptimizeClearCommand;
use Roots\Acorn\Console\Concerns\GracefullyCallsCommands;

class OptimizeClearCommand extends FoundationOptimizeClearCommand
{
    use GracefullyCallsCommands;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->components->info('Clearing cached bootstrap files.');

        foreach ($this->getOptimizeClearTasks() as $description => $command) {
            $this->components->task($description, fn () => $this->gracefulCallSilent($command) == 0);
        }

        $this->newLine();
    }
}
