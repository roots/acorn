<?php

namespace Roots\Acorn\Console\Commands;

use Roots\Acorn\Filesystem\Filesystem;

class ConfigClearCommand extends Command
{
   /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'config:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the configuration cache file';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new config clear command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

   /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->files->delete($this->app->getCachedConfigPath());

        $this->info('Configuration cache cleared!');
    }
}
