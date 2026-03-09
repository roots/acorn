<?php

namespace Roots\Acorn\Providers;

use Illuminate\Queue\Listener;
use Illuminate\Queue\Worker;
use Illuminate\Support\ServiceProvider;

class QueueServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->alias('queue.worker', Worker::class);

        $this->app->singleton('queue.listener', fn ($app) => new Listener($app->basePath()));
        $this->app->alias('queue.listener', Listener::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
