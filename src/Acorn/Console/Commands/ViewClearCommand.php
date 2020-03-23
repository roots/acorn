<?php

namespace Roots\Acorn\Console\Commands;

use Roots\Acorn\Filesystem\Filesystem;

class ViewClearCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'view:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears all compiled view files';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new view clear command instance.
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
        $path = $this->app['config']['view.compiled'];

        if (! $path) {
            $this->error('View path not found.');
        }

        $this->files->delete(
            $this->files->glob("{$path}/*")
        );

        $this->info('Compiled views cleared!');
    }
}
