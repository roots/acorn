<?php

namespace Roots\Acorn\Bootstrap;

use Illuminate\Contracts\Foundation\Application;

/**
 * @deprecated
 */
class SageFeatures
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Roots\Acorn\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        if (get_theme_support('sage')) {
            $app->register(\Roots\Acorn\Providers\SageFeaturesServiceProvider::class);
        }
    }
}
