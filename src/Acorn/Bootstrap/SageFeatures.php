<?php

namespace Roots\Acorn\Bootstrap;

use Illuminate\Contracts\Foundation\Application;

class SageFeatures
{
    /**
     * Sage Provider
     *
     * @var \Roots\Acorn\Sage\SageServiceProvider
     */
    protected $sage = \Roots\Acorn\Sage\SageServiceProvider::class;

    /**
     * A list of features to be loaded with Sage.
     *
     * @var array
     */
    protected $features = [];

    /**
     * A list of internal features to be loaded with Sage by default.
     *
     * @var array
     */
    protected $internalFeatures = [
        \Roots\Acorn\Assets\AssetsServiceProvider::class,
        \Roots\Acorn\View\ViewServiceProvider::class,
    ];

    /**
     * Bootstrap the given application.
     *
     * @param  \Roots\Acorn\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $this->features = $this->internalFeatures;

        if (! $features = apply_filters('acorn/sage.features', get_theme_support('sage'))) {
            return;
        };

        if (! empty($features) && is_array($features)) {
            $this->features = $features;
        }

        if (array_intersect($this->internalFeatures, $this->features)) {
            array_unshift($this->features, $this->sage);
        }

        foreach ($this->features as $feature) {
            $app->register($feature);
        }
    }
}
