<?php

namespace Roots\Acorn\Filesystem;

class FilesystemServiceProvider extends \Illuminate\Filesystem\FilesystemServiceProvider
{
    /**
     * Register the Filesystem natively inside of the provider.
     *
     * @return void
     */
    protected function registerNativeFilesystem()
    {
        $this->app->singleton('files', function () {
            return new Filesystem();
        });
    }
}
