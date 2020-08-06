<?php

namespace Roots\Acorn\Assets;

use Illuminate\Support\Str;

trait AssetsMixable
{
    /**
     * Get the URI to a Mix hot module replacement server.
     *
     * @link https://laravel-mix.com/docs/hot-module-replacement
     *
     * @param  string $path
     * @return string
     */
    protected function getMixHotUri(string $path): ?string
    {
        if (file_exists($path . '/hot')) {
            $url = rtrim(rtrim(file_get_contents($path . '/hot')), '/');

            return Str::after($url, ':');
        }

        return null;
    }
}
