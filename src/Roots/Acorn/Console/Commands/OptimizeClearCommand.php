<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Foundation\Console\OptimizeClearCommand as FoundationOptimizeClearCommand;

class OptimizeClearCommand extends FoundationOptimizeClearCommand
{
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->call('view:clear');
        $this->call('cache:clear');
        // $this->call('route:clear');
        $this->call('config:clear');
        $this->call('clear-compiled');

        $this->info('Caches cleared successfully!');
    }
}
