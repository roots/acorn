<?php

namespace Roots\Acorn\Bootstrap;

use Illuminate\Contracts\Foundation\Application;

class SageFeatures
{
    public function bootstrap(Application $app)
    {
        $features = get_theme_support('sage');
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
}
