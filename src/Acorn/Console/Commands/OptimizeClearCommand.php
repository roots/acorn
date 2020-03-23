<?php

namespace Roots\Acorn\Console\Commands;

class OptimizeClearCommand extends Command
{
   /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'optimize:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove cached framework files.';

   /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('config:clear');
        $this->call('view:clear');
        $this->call('package:clear');

        $this->info('Caches cleared successfully!');
    }
}
