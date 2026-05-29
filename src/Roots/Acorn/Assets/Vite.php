<?php

namespace Roots\Acorn\Assets;

use Illuminate\Foundation\Vite as FoundationVite;

use function Roots\asset;

class Vite extends FoundationVite
{
    /**
     * Generate an asset path for the application.
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    protected function assetPath($path, $secure = null)
    {
        $uri = str_replace('/build/build/', '/build/', asset($path)->uri());

        if (is_multisite()) {
            return home_url(parse_url($uri, PHP_URL_PATH));
        }

        return $uri;
    }
}
