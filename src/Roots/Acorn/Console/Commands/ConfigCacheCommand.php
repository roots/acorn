<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Container\Container;
use Illuminate\Foundation\Console\ConfigCacheCommand as FoundationConfigCacheCommand;
use Illuminate\Support\Facades\Facade;
use Roots\Acorn\Console\Concerns\GetsFreshApplication;

class ConfigCacheCommand extends FoundationConfigCacheCommand
{
    use GetsFreshApplication {
        getFreshConfiguration as getPristineConfiguration;
    }

    /**
     * Get a fresh copy of the application configuration.
     *
     * Nonexistent providers are filtered out, and the container and facades
     * are pointed back at this application afterwards.
     *
     * @return array
     */
    protected function getFreshConfiguration()
    {
        try {
            $config = $this->getPristineConfiguration();
        } finally {
            Facade::clearResolvedInstances();
            Facade::setFacadeApplication($this->laravel);
            Container::setInstance($this->laravel);
        }

        $config['app']['providers'] = array_filter($config['app']['providers'], 'class_exists');

        return $config;
    }
}
