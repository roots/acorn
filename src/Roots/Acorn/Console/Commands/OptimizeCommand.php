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
        $this->gracefulCall('config:cache');
        $this->gracefulCall('route:cache');

        $this->info('Files cached successfully!');
    }
}
