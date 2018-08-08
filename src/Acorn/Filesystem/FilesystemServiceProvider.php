<?php

namespace Roots\Acorn\Filesystem;

class FilesystemServiceProvider extends \Illuminate\Filesystem\FilesystemServiceProvider
{
    protected function registerNativeFilesystem()
    {
        $this->app->singleton('files', function () {
            return new Filesystem();
        });
    }
}
