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

        collect([
            'config' => fn () => $this->gracefulCallSilent('config:cache') == 0,
            'events' => fn () => $this->gracefulCallSilent('event:cache') == 0,
            'routes' => fn () => $this->gracefulCallSilent('route:cache') == 0,
            'views' => fn () => $this->gracefulCallSilent('view:cache') == 0,
        ])->each(fn ($task, $description) => $this->components->task($description, $task));

        $this->newLine();
    }
}
