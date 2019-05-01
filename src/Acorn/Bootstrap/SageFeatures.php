<?php

namespace Roots\Acorn\Bootstrap;

use function Roots\add_filters;
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
            $features = ['assets', 'blade', 'structure'];
        }
        return $features;
    }

    protected function featureAssets(Application $app)
    {
        $app->register(\Roots\Acorn\Assets\ManifestServiceProvider::class);
    }

    protected function featureBlade(Application $app)
    {
        $app->register(\Roots\Acorn\View\ViewServiceProvider::class);
    }

    protected function featureStructure(Application $app)
    {
        add_filters([
            'theme_file_path',
            'theme_file_uri',
            'parent_theme_file_path',
            'parent_theme_file_uri',
        ], function ($path, $file) {
            if (empty($file)) {
                return dirname($path);
            }

            $path = preg_replace("#{$file}$#", '', $path);
            return dirname($path) . '/' . $file;
        }, 10);
    }
}
