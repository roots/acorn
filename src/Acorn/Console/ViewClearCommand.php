<?php

namespace Roots\Acorn\Console;

use Roots\Acorn\Filesystem\Filesystem;

class ViewClearCommand extends Command
{
    /**
     * Clear all compiled view files
     *
     * ## EXAMPLES
     *
     *     wp acorn view:clear
     *
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
