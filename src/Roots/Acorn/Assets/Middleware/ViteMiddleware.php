<?php

namespace Roots\Acorn\Assets\Middleware;

use Illuminate\Support\Facades\Vite;

class ViteMiddleware
{
    /**
     * Handle the manifest config.
     *
     * @param  array  $config
     * @return array
     */
    public function handle($config)
    {
        if (! Vite::manifestHash() && ! Vite::isRunningHot()) {
            return $config;
        }

        if (str_contains($config['path'], '/build')) {
            return $config;
        }

        return [
            'url' => get_theme_file_uri('public/build'),
            'path' => public_path('build'),
            'bundles' => public_path('build/manifest.json'),
            'assets' => public_path('build/manifest.json'),
        ];
    }
}
