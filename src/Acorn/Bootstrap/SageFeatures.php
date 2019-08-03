<?php

namespace Roots\Acorn\Bootstrap;

use Illuminate\Contracts\Foundation\Application;

class SageFeatures
{
    public function bootstrap(Application $app)
    {
        if (! $features = $this->getFeatures()) {
            return;
        }

        $app->register(\Roots\Acorn\Sage\SageServiceProvider::class);

        foreach ($features as $feature) {
            $this->{'feature' . ucfirst($feature)}($app);
        }
    }

    protected function getFeatures()
    {
        $features = apply_filters('acorn/sage.features', get_theme_support('sage'));
        if ($features === true) {
            $features = ['assets', 'blade'];
        }
        return $features;
    }

    protected function featureAssets(Application $app)
    {
        $app->register(\Roots\Acorn\Assets\AssetsServiceProvider::class);
    }

    protected function featureBlade(Application $app)
    {
        $app->register(\Roots\Acorn\View\ViewServiceProvider::class);
    }
}
