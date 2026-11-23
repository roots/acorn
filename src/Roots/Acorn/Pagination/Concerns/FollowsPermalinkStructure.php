<?php

namespace Roots\Acorn\Pagination\Concerns;

trait FollowsPermalinkStructure
{
    /**
     * Get the base path for paginator generated URLs.
     *
     * Laravel strips the trailing slash from the path, so on WordPress
     * requests it's added back to match the permalink structure.
     * Otherwise, `redirect_canonical()` redirects every page link.
     *
     * @return string|null
     */
    public function path()
    {
        $path = parent::path();

        if (! is_string($path) || str_contains($path, '?') || ! $this->followsPermalinkStructure()) {
            return $path;
        }

        return user_trailingslashit($path);
    }

    /**
     * Determine if the paginator should follow the WordPress permalink structure.
     */
    protected function followsPermalinkStructure(): bool
    {
        if (! function_exists('user_trailingslashit') || ! isset($GLOBALS['wp_rewrite'])) {
            return false;
        }

        $route = app()->bound('request') ? app('request')->route() : null;

        return ! $route || $route->getName() === 'wordpress';
    }
}
