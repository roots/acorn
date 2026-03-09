<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Foundation\Console\OptimizeCommand as FoundationOptimizeCommand;
use Roots\Acorn\Console\Concerns\GracefullyCallsCommands;

class OptimizeCommand extends FoundationOptimizeCommand
{
    use GracefullyCallsCommands;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->components->info('Caching framework bootstrap, configuration, and metadata.');

        foreach ($this->getOptimizeTasks() as $description => $command) {
            $this->components->task($description, fn () => $this->gracefulCallSilent($command) == 0);
        }

        $this->newLine();
    }
}
