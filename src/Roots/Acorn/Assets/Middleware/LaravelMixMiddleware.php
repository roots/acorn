<?php

namespace Roots\Acorn\Assets\Middleware;

use Illuminate\Support\Str;

class LaravelMixMiddleware
{
    /**
     * Handle the manifest config.
     *
     * @param array $config
     * @return array
     */
    public function handle($config)
    {
        if ($url = $this->getMixHotUri($config['path'])) {
            $config['url'] = $url;
        }

        return $config;
    }

    /**
     * Get the URI to a Mix hot module replacement server.
     *
     * @link https://laravel-mix.com/docs/hot-module-replacement
     *
     * @param  string $path
     * @return string|null
     */
    protected function getMixHotUri(string $path): ?string
    {
        if (! file_exists($hot = "{$path}/hot")) {
            return null;
        }

        $url = rtrim(rtrim(file_get_contents($hot)), '/');

        return Str::after($url, ':');
    }
}
