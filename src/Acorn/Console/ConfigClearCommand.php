<?php

namespace Roots\Acorn\Console;

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
