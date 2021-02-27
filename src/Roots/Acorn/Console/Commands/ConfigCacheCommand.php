<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Console\ConfigCacheCommand as FoundationConfigCacheCommand;
use Roots\Acorn\Bootloader;

class ConfigCacheCommand extends FoundationConfigCacheCommand
{
    /**
     * Boot a fresh copy of the application configuration.
     *
     * @return array
     */
    protected function getFreshConfiguration()
    {
        $app = $this->getLaravel();

        (new Bootloader(
            ['acorn/fresh-config'],
            get_class($this->getLaravel())
        ))->call(function (Application $newApp) use (&$app) {
            $app = $newApp;
        });

        do_action('acorn/fresh-config');

        return $app->make('config')->all();
    }
}
