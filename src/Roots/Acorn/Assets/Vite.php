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

        if (! is_multisite()) {
            return $uri;
        }

        return $this->rewriteHost($uri, home_url());
    }

    /**
     * Swap the scheme/host/port of a URI to match home_url(), preserving the
     * original path/query/fragment.
     *
     * Assumes assets are served from the same origin as the site.
     *
     * @param  string  $uri  The URI to rewrite.
     * @param  string  $base  The base URL to use for the rewrite.
     * @return string  The rewritten URI.
     */
    protected function rewriteHost(string $uri, string $base): string
    {
        $home = parse_url($base);
        $parts = parse_url($uri);

        if (empty($home['host']) || empty($parts['host'])) {
            return $uri;
        }

        $origin = ($home['scheme'] ?? $parts['scheme'] ?? 'https').'://'.$home['host'];

        if (! empty($home['port'])) {
            $origin .= ':'.$home['port'];
        }

        return $origin
            .($parts['path'] ?? '')
            .(isset($parts['query']) ? '?'.$parts['query'] : '')
            .(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
    }
}
