<?php

namespace Roots\Acorn\Assets\Middleware;

use Illuminate\Support\Str;

class RootsBudMiddleware
{
    /**
     * The Bud dev server origin header.
     *
     * @var string
     */
    protected $devOrigin;

    /**
     * Create a new Bud middleware instance.
     *
     * @return void
     */
    public function __construct(?string $devOrigin = null)
    {
        $this->devOrigin = $devOrigin;
    }

    /**
     * Handle the manifest config.
     *
     * @param  array  $config
     * @return array
     */
    public function handle($config)
    {
        if ($url = $this->getBudDevUri($config['path'])) {
            $config['url'] = $url;
        }

        return $config;
    }

    /**
     * Get the URI to a Bud hot module replacement server.
     *
     * @link https://budjs.netlify.app/docs/bud.serve
     */
    protected function getBudDevUri(string $path): ?string
    {
        if (! $path = realpath("{$path}/hmr.json")) {
            return null;
        }

        if (! $devOriginHeader = $this->getDevOriginHeader()) {
            return null;
        }

        if (! $dev = optional(json_decode(file_get_contents($path)))->dev) {
            return null;
        }

        if (strstr($devOriginHeader, $dev->hostname) === false) {
            return null;
        }

        return Str::after(rtrim($dev->href, '/'), ':');
    }

    /**
     * Get the Bud dev server origin header.
     *
     * @return string|null|false
     */
    protected function getDevOriginHeader()
    {
        return $this->devOrigin
            ?: filter_input(INPUT_ENV, 'HTTP_X_BUD_DEV_ORIGIN', FILTER_SANITIZE_URL)
            ?: filter_input(INPUT_SERVER, 'HTTP_X_BUD_DEV_ORIGIN', FILTER_SANITIZE_URL);
    }
}
