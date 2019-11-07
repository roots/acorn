<?php

namespace Roots\Acorn\Assets\Contracts;

interface Asset
{
    /**
     * Get the asset's remote URI
     *
     * Example: https://example.com/app/themes/sage/dist/styles/a1b2c3.min.css
     *
     * @return string
     */
    public function uri(): string;

    /**
     * Get the asset's local path
     *
     * Example: /srv/www/example.com/current/web/app/themes/sage/dist/styles/a1b2c3.min.css
     *
     * @return string
     */
    public function path(): string;

    /**
     * Check whether the asset exists on the file system
     *
     * @return bool
     */
    public function exists(): bool;

    /**
     * Get the contents of the asset
     *
     * @return string|false
     */
    public function contents();
}
