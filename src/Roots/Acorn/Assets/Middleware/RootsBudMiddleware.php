<?php

namespace Roots\Acorn\Assets\Middleware;

use Illuminate\Support\Str;

class RootsBudMiddleware
{
    /**
     * Dev server URI
     *
     * @var string
     */
    protected $dev_origin;

    public function __construct(?string $dev_origin = null)
    {
        $this->dev_origin = $dev_origin;
    }

    /**
     * Handle the manifest config.
     *
     * @param array $config
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
     *
     * @param  string $path
     * @return string|null
     */
    protected function getBudDevUri(string $path): ?string
    {
        if (! $path = realpath("{$path}/hmr.json")) {
            return null;
        }

        if (! $dev_origin_header = $this->getDevOriginHeader()) {
            return null;
        }

        if (! $dev = optional(json_decode(file_get_contents($path)))->dev) {
            return null;
        }

        if (strstr($dev_origin_header, $dev->hostname) === false) {
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
        return $this->dev_origin
            ?: filter_input(INPUT_ENV, 'HTTP_X_BUD_DEV_ORIGIN', FILTER_SANITIZE_URL)
            ?: filter_input(INPUT_SERVER, 'HTTP_X_BUD_DEV_ORIGIN', FILTER_SANITIZE_URL);
    }
}
