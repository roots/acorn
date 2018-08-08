<?php

namespace Roots\Acorn;

use Illuminate\Support\ServiceProvider as ServiceProviderBase;

abstract class ServiceProvider extends ServiceProviderBase
{
    protected function mergeConfigFrom($path, $key)
    {
        $this->app['config']->load($path, $key);
    }
}
