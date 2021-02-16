<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Foundation\Console\ConfigCacheCommand as FoundationConfigCacheCommand;

use function Roots\app;

class ConfigCacheCommand extends FoundationConfigCacheCommand
{
    /**
     * Boot a fresh copy of the application configuration.
     *
     * @return array
     */
    protected function getFreshConfiguration()
    {
        return app()->make('config')->all();
    }
}
