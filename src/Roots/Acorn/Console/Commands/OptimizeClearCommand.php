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
        $this->gracefulCall('view:clear');
        $this->gracefulCall('cache:clear');
        $this->gracefulCall('route:clear');
        $this->gracefulCall('config:clear');
        $this->gracefulCall('clear-compiled');

        $this->info('Caches cleared successfully!');
    }
}
