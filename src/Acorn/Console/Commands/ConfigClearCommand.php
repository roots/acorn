<?php

namespace Roots\Acorn\Console\Commands;

use Roots\Acorn\Console\Command;

class ConfigClearCommand extends Command
{
    /**
     * Remove the configuration cache file
     *
     * ## EXAMPLES
     *
     *     wp acorn config:clear
     *
     * @return void
     */
    public function __invoke($args, $assoc_args)
    {
        $this->files->delete($this->app->getCachedConfigPath());

        $this->success('Configuration cache cleared!');
    }
}
