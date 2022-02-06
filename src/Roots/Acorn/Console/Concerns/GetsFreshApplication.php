<?php

namespace Roots\Acorn\Console\Concerns;

use Illuminate\Contracts\Foundation\Application;
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
        $applicationClass = get_class($app = Bootloader::getInstance()->getApplication());

        return (new $bootloaderClass(new $applicationClass(
            $app->basePath(),
            $this->getApplicationPaths($app)
        )))->getApplication();
    }

    /**
     * Boot a fresh copy of the application configuration.
     *
     * @return array
     */
    protected function getFreshConfiguration()
    {
        $app = $this->getFreshApplication();

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app->make('config')->all();
    }

    /**
     * Get all of the configured paths for the Application.
     *
     * @param Application $app
     * @return array
     */
    protected function getApplicationPaths(Application $app)
    {
        return [
            'app' => method_exists($app, 'path') ? $app->path() : $app->make('path'),
            'lang' => method_exists($app, 'langPath') ? $app->langPath() : $app->make('path.lang'),
            'config' => $app->configPath(),
            'public' => method_exists($app, 'publicPath') ? $app->publicPath() : $app->make('path.public'),
            'storage' => $app->storagePath(),
            'database' => $app->databasePath(),
            'resources' => $app->resourcePath(),
            'bootstrap' => $app->bootstrapPath(),
        ];
    }
}
