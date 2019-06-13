<?php

namespace Roots\Acorn\Console\Commands;

use Roots\Acorn\Console\Command;

class OptimizeCommand extends Command
{
    /**
     * Generate and cache framework files.
     *
     * ## EXAMPLES
     *
     *     wp acorn optimize
     *
     */
    public function __invoke($args, $assoc_args)
    {
        $this->call('config:cache');
        $this->call('view:cache');
        $this->call('package:discover');

        $this->success('Files cached successfully!');
    }
}
