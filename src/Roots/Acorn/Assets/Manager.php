<?php

namespace Roots\Acorn\Assets;

use InvalidArgumentException;
use Roots\Acorn\Assets\Concerns\Mixable;
use Roots\Acorn\Assets\Contracts\Manifest as ManifestContract;

/**
 * Manage assets manifests
 *
 * @see \Illuminate\Support\Manager
 * @link https://github.com/illuminate/support/blob/8.x/Manager.php
 */
class Manager
{
    use Mixable;

    /**
     * Resolved manifests
     *
     * @var ManifestContract[]
     */
    protected $manifests;

    /**
     * Assets Config
     *
     * @var array
     */
    protected $config;

    /**
     * Initialize the AssetManager instance.
     *
     * @param Container $container
     */
    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * Register the given manifest
     *
     * @param  string $name
     * @param  Manifest $manifest
     * @return static
     */
    public function register(string $name, ManifestContract $manifest): self
    {
        $this->manifests[$name] = $manifest;

        return $this;
    }

    /**
     * Get a Manifest
     *
     * @param  string $name
     * @param  array $config
     * @return ManifestContract
     */
    public function manifest(string $name, ?array $config = null): ManifestContract
    {
        $manifest = $this->manifests[$name] ?? $this->resolve($name, $config);

        return $this->manifests[$name] = $manifest;
    }

    /**
     * Resolve the given manifest.
     *
     * @param  string  $name
     * @return ManifestContract
     *
     * @throws InvalidArgumentException
     */
    protected function resolve(string $name, ?array $config): ManifestContract
    {
        $config = $config ?? $this->getConfig($name);

        if (isset($config['handler'])) {
            return new $config['handler']($config);
        }

        $url = $this->getMixHotUri($path = $config['path']) ?? $config['url'];
        $assets = isset($config['assets']) ? $this->getJsonManifest($config['assets']) : [];
        $bundles = isset($config['bundles']) ? $this->getJsonManifest($config['bundles']) : [];

        return new Manifest($path, $url, $assets, $bundles);
    }

    /**
     * Opens a JSON manifest file from the local file system
     *
     * @param string $jsonManifest Path to .json file
     * @return array
     */
    protected function getJsonManifest(string $jsonManifest): array
    {
        return file_exists($jsonManifest) ? json_decode(file_get_contents($jsonManifest), true) : [];
    }

    /**
     * Get the assets manifest configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig(string $name): array
    {
        return $this->config['manifests'][$name];
    }
}
