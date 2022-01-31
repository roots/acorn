<?php

namespace Roots\Acorn\Console\Concerns;

use Roots\Acorn\Bootloader;

trait GetsFreshApplication
{
    /**
     * Get a fresh application instance.
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    protected function getFreshApplication()
    {
        $bootloaderClass = get_class(Bootloader::getInstance());

        return (new $bootloaderClass())->getApplication();
    }

    /**
     * Boot a fresh copy of the application configuration.
     *
     * @return array
     */
    protected function getFreshConfiguration()
    {
        return $this->getFreshApplication()->make('config')->all();
    }
}
