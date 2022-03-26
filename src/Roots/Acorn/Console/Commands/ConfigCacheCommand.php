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
        add_filter('acorn/ready', '__return_true');

        /** @var \Illuminate\Contracts\Foundation\Application */
        $app = null;

        $appClass = get_class($this->getLaravel());
        $appClass::setInstance(null);

        $bootloader = new Bootloader([], $appClass);

        $bootloader();

        $bootloader->call(function (Application $newApp) use (&$app) {
            $app = $newApp;
        });

        return $app->make('config')->all();
    }
}
