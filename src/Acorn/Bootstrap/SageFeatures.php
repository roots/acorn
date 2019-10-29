<?php

namespace Roots\Acorn\Bootstrap;

use Illuminate\Contracts\Foundation\Application;

class SageFeatures
{
    /**
     * Sage Features
     *
     * @var array
     */
    protected $features = [
        'sage' => \Roots\Acorn\Sage\SageServiceProvider::class,
        'assets' => \Roots\Acorn\Assets\AssetsServiceProvider::class,
        'blade' => \Roots\Acorn\View\ViewServiceProvider::class,
    ];

    /**
     * Bootstrap the given application.
     *
     * @param  \Roots\Acorn\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        if (! $features = apply_filters('acorn/sage.features', get_theme_support('sage'))) {
            return;
        };

        if (is_array($features)) {
            $this->features = $features;
        }

        if (! $this->features) {
            return;
        }

        foreach ($this->features as $feature) {
            $app->register($feature);
        }
    }
}
