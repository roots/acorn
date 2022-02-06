<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Foundation\Console\ConfigCacheCommand as FoundationConfigCacheCommand;
use Roots\Acorn\Console\Concerns\GetsFreshApplication;

class ConfigCacheCommand extends FoundationConfigCacheCommand
{
    use GetsFreshApplication {
        getFreshConfiguration as getPristineConfiguration;
    }

    /**
     * Get a fresh copy of the application configuration.
     *
     * Nonexistent providers are filtered out.
     *
     * @return array
     */
    protected function getFreshConfiguration()
    {
        $config = $this->getPristineConfiguration();

        $config['app']['providers'] = array_filter($config['app']['providers'], 'class_exists');

        return $config;
    }
}
