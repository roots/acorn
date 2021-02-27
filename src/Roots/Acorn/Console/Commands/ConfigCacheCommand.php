<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Foundation\Console\ConfigCacheCommand as FoundationConfigCacheCommand;

class ConfigCacheCommand extends FoundationConfigCacheCommand
{
    /**
     * Boot a fresh copy of the application configuration.
     *
     * @return array
     */
    protected function getFreshConfiguration()
    {
        return $this->getLaravel()->make('config')->all();
    }
}
