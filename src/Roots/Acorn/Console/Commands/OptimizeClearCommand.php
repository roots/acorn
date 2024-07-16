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

        collect([
            'cache' => fn () => $this->gracefulCallSilent('cache:clear') == 0,
            'compiled' => fn () => $this->gracefulCallSilent('clear-compiled') == 0,
            'config' => fn () => $this->gracefulCallSilent('config:clear') == 0,
            'events' => fn () => $this->gracefulCallSilent('event:clear') == 0,
            'routes' => fn () => $this->gracefulCallSilent('route:clear') == 0,
            'views' => fn () => $this->gracefulCallSilent('view:clear') == 0,
        ])->each(fn ($task, $description) => $this->components->task($description, $task));

        $this->newLine();
    }
}
