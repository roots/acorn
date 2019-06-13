<?php

namespace Roots\Acorn\Console\Commands;

use Roots\Acorn\Console\Command;

class OptimizeClearCommand extends Command
{
    /**
     * Remove cached framework files.
     *
     * ## EXAMPLES
     *
     *     wp acorn optimize:clear
     *
     */
    public function __invoke($args, $assoc_args)
    {
        $this->call('config:clear');
        $this->call('view:clear');
        $this->call('package:clear');

        $this->success('Caches cleared successfully!');
    }
}
