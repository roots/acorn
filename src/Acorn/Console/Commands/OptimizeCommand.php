<?php

namespace Roots\Acorn\Console\Commands;

class OptimizeCommand extends Command
{
   /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'optimize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and cache framework files';

   /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('config:cache');
        $this->call('view:cache');
        $this->call('package:discover');

        $this->info('Files cached successfully!');
    }
}
