<?php

namespace Roots\Acorn\Console\Commands;

class PackageClearCommand extends Command
{
   /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'package:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove compiled service and package files.';

   /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (file_exists($servicesPath = $this->app->getCachedServicesPath())) {
            @unlink($servicesPath);
        }

        if (file_exists($packagesPath = $this->app->getCachedPackagesPath())) {
            @unlink($packagesPath);
        }

        $this->info('Compiled services and packages files removed!');
    }
}
