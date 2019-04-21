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

        if ($features === true) {
            $features = ['assets', 'blade'];
        }

        if (in_array('assets', $features)) {
            $app->register(\Roots\Acorn\Assets\ManifestServiceProvider::class);
        }

        if (in_array('blade', $features)) {
            $app->register(\Roots\Acorn\View\ViewServiceProvider::class);
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
}
