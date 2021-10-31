<?php

namespace Roots\Acorn\Assets;

use Illuminate\Support\Str;
use Roots\Acorn\Assets\Contracts\Asset as AssetContract;
use Roots\Acorn\Assets\Contracts\Bundle as BundleContract;
use Roots\Acorn\Assets\Contracts\Manifest as ManifestContract;
use Roots\Acorn\Assets\AssetFactory;

class Manifest implements ManifestContract
{
    protected $assets;

    protected $bundles;

    protected $path;

    protected $uri;

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
     * @param string $key
     * @return AssetContract
     */
    public function asset($key): AssetContract
    {
        $key = $this->normalizeRelativePath($key);
        $relative_path = $this->assets[$key] ?? $key;
        $path = Str::before("{$this->path}/{$relative_path}", '?');
        $uri = "{$this->uri}/{$relative_path}";

        return AssetFactory::create($path, $uri);
    }

    /**
     * Get specified bundles.
     *
     * @param string $key
     * @return BundleContract
     */
    public function bundle($key): BundleContract
    {
        return new Bundle($key, $this->bundles[$key], $this->path, $this->uri);
    }

    /**
     * Normalizes to forward slashes and removes leading slash.
     *
     * @return string
     */
    protected function normalizeRelativePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('%//+%', '/', $path);

        return ltrim($path, './');
    }
}
