<?php

namespace Roots\Acorn\Console\Commands;

use Roots\Acorn\Console\Command;
use Roots\Acorn\Filesystem\Filesystem;

class PackageClearCommand extends Command
{
    /**
     * Remove compiled services and packages files.
     *
     * ## EXAMPLES
     *
     *     wp acorn package:clear
     *
     * @return void
     */
    public function __invoke($args, $assoc_args)
    {
        if (file_exists($servicesPath = $this->app->getCachedServicesPath())) {
            @unlink($servicesPath);
        }

        if (file_exists($packagesPath = $this->app->getCachedPackagesPath())) {
            @unlink($packagesPath);
        }

        $this->success('Compiled services and packages files removed!');
    }
}
