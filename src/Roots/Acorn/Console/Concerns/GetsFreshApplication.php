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
            ->withPaths(...$this->getApplicationPaths($app))
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
            'app' => $app->path(),
            'config' => $app->configPath(),
            'storage' => $app->storagePath(),
            'resources' => $app->resourcePath(),
            'public' => $app->publicPath(),
            'bootstrap' => $app->bootstrapPath(),
            'lang' => $app->langPath(),
            'database' => $app->databasePath(),
        ];
    }
}
