<?php

namespace Roots\Acorn\Sage;

use function Roots\add_filters;
use Roots\Acorn\ServiceProvider;
use Roots\Acorn\Sage\ViewFinder;
use Roots\Acorn\Sage\Sage;
use Roots\Acorn\Config;

class SageServiceProvider extends ServiceProvider
{
    /** {@inheritDoc} */
    public function register()
    {
        $this->app->singleton('sage', Sage::class);
        $this->app->bind('sage.finder', ViewFinder::class);
        $this->registerThemeFolderFilters();
    }

    public function registerThemeFolderFilters($priority = 10)
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
        }, $priority);
    }

    public function boot()
    {
        if ($this->app->bound('view')) {
            $this->app['sage']->attach();
        }
    }
}
