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

        if (! is_string($path) || ! $this->followsPermalinkStructure($path)) {
            return $path;
        }

        $slashed = user_trailingslashit($path);

        return str_ends_with($slashed, '/') ? $slashed : $path;
    }

    /**
     * Determine if the path should follow the WordPress permalink structure.
     *
     * Only front-end permalinks are changed. Admin, AJAX, REST, and file
     * paths like `wp-login.php` are left alone, as are Laravel routes.
     */
    protected function followsPermalinkStructure(string $path): bool
    {
        if (! function_exists('user_trailingslashit') || ! isset($GLOBALS['wp_rewrite'])) {
            return false;
        }

        if (str_contains($path, '?') || str_contains(basename((string) parse_url($path, PHP_URL_PATH)), '.')) {
            return false;
        }

        if (function_exists('is_admin') && is_admin() || defined('REST_REQUEST') && REST_REQUEST) {
            return false;
        }

        $route = app()->bound('request') ? app('request')->route() : null;

        return ! $route || $route->getName() === 'wordpress';
    }
}
