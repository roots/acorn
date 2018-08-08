<?php

namespace Roots\Acorn\Assets;

use Roots\Acorn\Contracts\Assets\Asset as AssetContract;

class Asset implements AssetContract
{
    /** @var \Roots\Acorn\Assets\Manifest */
    protected $manifest;

    /** @var string */
    protected $path;

    /**
     * Get asset from manifest
     *
     * @param  string $path Relative path of the asset before cache-busting
     * @param  \Roots\Acorn\Assets\Manifest
     * @return \Roots\Acorn\Assets\Asset
     */
    public function __construct(string $path, Manifest $manifest)
    {
        $this->path     = $path;
        $this->manifest = $manifest;
    }

    /**
     * Get the manifest that references the asset
     *
     * @return \Roots\Acorn\Assets\Manifest
     */
    public function getManifest()
    {
        return $this->manifest;
    }

    /**
     * Get the asset's original relative path
     *
     * Example: styles/main.css
     *
     * @return string
     */
    public function original()
    {
        return $this->path;
    }

    /**
     * Get the asset's cache-busted relative path
     *
     * Example: styles/a1b2c3.min.css
     *
     * @return string
     */
    public function revved()
    {
        return ($this->manifest[$this->path] ?? $this->original());
    }

    /**
     * Get the asset's remote URI
     *
     * Example: https://example.com/app/themes/sage/dist/styles/a1b2c3.min.css
     *
     * @return string
     */
    public function uri()
    {
        return "{$this->manifest->uri()}/{$this->revved()}";
    }

    /**
     * Get the asset's local path
     *
     * Example: /srv/www/example.com/current/web/app/themes/sage/dist/styles/a1b2c3.min.css
     *
     * @return string
     */
    public function path()
    {
        return "{$this->manifest->path()}/{$this->revved()}";
    }

    /** {@inheritdoc} */
    public function __toString()
    {
        return $this->uri();
    }
}
