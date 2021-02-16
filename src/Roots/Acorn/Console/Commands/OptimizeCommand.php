<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Foundation\Console\OptimizeCommand as FoundationOptimizeCommand;

class OptimizeCommand extends FoundationOptimizeCommand
{
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->call('config:cache');
        // $this->call('route:cache');

        $this->info('Files cached successfully!');
    }
}
