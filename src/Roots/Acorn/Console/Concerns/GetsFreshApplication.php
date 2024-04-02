<?php

namespace Roots\Acorn\Console\Concerns;

use Roots\Acorn\Application;

trait GetsFreshApplication
{
    /**
     * Get a fresh application instance.
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    protected function getFreshApplication()
    {
        $application = get_class($app = Application::getInstance());

        return $application::configure($app->basePath())
            ->withPaths($this->getApplicationPaths($app))
            ->boot();
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
