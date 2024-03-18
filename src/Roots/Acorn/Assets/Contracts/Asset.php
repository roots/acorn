<?php

namespace Roots\Acorn\Assets\Contracts;

interface Asset
{
    /**
     * Get the asset's remote URI
     *
     * Example: https://example.com/app/themes/sage/dist/styles/a1b2c3.min.css
     */
    public function uri(): string;

    /**
     * Get the asset's local path
     *
     * Example: /srv/www/example.com/current/web/app/themes/sage/dist/styles/a1b2c3.min.css
     */
    public function path(): string;

    /**
     * Check whether the asset exists on the file system
     */
    public function exists(): bool;

    /**
     * Get the contents of the asset
     *
     * @return mixed
     */
    public function contents();

    /**
     * Get the relative path to the asset.
     *
     * @param  string  $base_path  Base path to use for relative path.
     */
    public function relativePath(string $base_path): string;

    /**
     * Get data URL of asset.
     *
     * @return string
     */
    public function dataUrl();
}
