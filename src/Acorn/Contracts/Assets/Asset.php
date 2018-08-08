<?php

namespace Roots\Acorn\Contracts\Assets;

interface Asset
{
    /**
     * Get the asset's original relative path
     *
     * Example: styles/main.css
     *
     * @return string
     */
    public function original();

    /**
     * Get the asset's cache-busted relative path
     *
     * Example: styles/a1b2c3.min.css
     *
     * @return string
     */
    public function revved();

    /**
     * Get the asset's remote URI
     *
     * Example: https://example.com/app/themes/sage/dist/styles/a1b2c3.min.css
     *
     * @return string
     */
    public function uri();

    /**
     * Get the asset's local path
     *
     * Example: /srv/www/example.com/current/web/app/themes/sage/dist/styles/a1b2c3.min.css
     *
     * @return string
     */
    public function path();

    /** {@inheritdoc} */
    public function __toString();
}
