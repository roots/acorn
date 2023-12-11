<?php

namespace Roots\Acorn\Assets;

use Illuminate\Support\Str;
use Roots\Acorn\Assets\Contracts\Asset as AssetContract;
use Roots\Acorn\Assets\Contracts\Bundle as BundleContract;
use Roots\Acorn\Assets\Contracts\Manifest as ManifestContract;

class Manifest implements ManifestContract
{
    /**
     * The manifest assets.
     *
     * @var array
     */
    protected $assets;

    /**
     * The manifest bundles.
     *
     * @var array
     */
    protected $bundles;

    /**
     * The manifest path.
     *
     * @var string
     */
    protected $path;

    /**
     * The manifest URI.
     *
     * @var string
     */
    protected $uri;

    /**
     * Create a new manifest instance.
     */
    public function __construct(string $path, string $uri, array $assets = [], ?array $bundles = null)
    {
        $this->path = $path;
        $this->uri = $uri;
        $this->bundles = $bundles;

        foreach ($assets as $original => $revved) {
            $this->assets[$this->normalizeRelativePath($original)] = $this->normalizeRelativePath($revved);
        }
    }

    /**
     * Get specified asset.
     *
     * @param  string  $key
     */
    public function asset($key): AssetContract
    {
        $key = $this->normalizeRelativePath($key);
        $relativePath = $this->assets[$key] ?? $key;

        $path = Str::before("{$this->path}/{$relativePath}", '?');
        $uri = "{$this->uri}/{$relativePath}";

        return AssetFactory::create($path, $uri);
    }

    /**
     * Get specified bundles.
     *
     * @param  string  $key
     */
    public function bundle($key): BundleContract
    {
        return new Bundle($key, $this->bundles[$key], $this->path, $this->uri);
    }

    /**
     * Normalizes to forward slashes and removes leading slash.
     */
    protected function normalizeRelativePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('%//+%', '/', $path);

        return ltrim($path, './');
    }
}
