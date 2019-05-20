<?php

namespace Roots\Acorn\Console;

use function Roots\config;
use Roots\Acorn\Filesystem\Filesystem;

class ViewClearCommand extends Command
{
    /**
     * Clear all compiled view files
     */
    public function __invoke($args, $assoc_args)
    {
        $path = $this->app['config']['view.compiled'];

        if (! $path) {
            $this->error('View path not found.');
        }

        $this->files->delete($this->files->glob("{$path}/*"));
        $this->success('Compiled views cleared!');
    }
}
