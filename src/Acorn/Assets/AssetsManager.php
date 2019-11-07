<?php

namespace Roots\Acorn\Assets;

use Psr\Container\ContainerInterface;
use Roots\Acorn\Assets\Contracts\Manifest;
use Roots\Acorn\Assets\RelativePathManifest;

/**
 * Manage assets manifests
 *
 * @see \Illuminate\Support\Manager
 * @link https://github.com/illuminate/support/blob/v5.8.27/Manager.php
 */
class AssetsManager
{
    /**
     * Application container
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * Resolved manifests
     *
     * @var \Roots\Acorn\Assets\Contracts\Manifest[]
     */
    protected $manifests;

    /**
     * Registered custom manifest creators
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * Initialize the AssetManager instance.
     *
     * @param ContainerInterface $app
     */
    public function __construct(ContainerInterface $app)
    {
        $this->app = $app;
    }

    /**
     * Register the given manifest
     *
     * @param  string $name
     * @param  \Roots\Acorn\Assets\Contracts\Manifest $manifest
     * @return static
     */
    public function register(string $name, Manifest $manifest): self
    {
        $this->manifests[$name] = $manifest;

        return $this;
    }

    /**
     * Get a Manifest
     *
     * @param  string $name
     * @param  array $config
     * @return \Roots\Acorn\Assets\Contracts\Manifest
     */
    public function manifest(string $name = null, array $config = null): Manifest
    {
        $name = $name ?: $this->getDefaultManifest();

        $manifest = $this->manifests[$name] ?? $this->resolve($name, $config);

        return $this->manifests[$name] = $manifest;
    }

    /**
     * Resolve the given manifest.
     *
     * @param  string  $name
     * @return \Roots\Acorn\Assets\Contracts\Manifest
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve(string $name, ?array $config): Manifest
    {
        $config = $config ?? $this->getConfig($name);
        $strategy = $config['strategy'] ?? 'relative';

        if (isset($this->customCreators[$strategy])) {
            return $this->callCustomCreator($config);
        }

        $strategyMethod = 'create' . ucfirst($strategy) . 'Manifest';

        if (method_exists($this, $strategyMethod)) {
            return $this->{$strategyMethod}($config);
        }

        throw new InvalidArgumentException("Strategy [{$strategy}] is not supported.");
    }

    /**
     * Call a custom manifest creator.
     *
     * @param  array  $config
     * @return \Roots\Acorn\Assets\Contracts\Manifest
     */
    protected function callCustomCreator(array $config): Manifest
    {
        return $this->customCreators[$config['strategy']]($this->app, $config);
    }

    /**
     * Gets an asset manifest from a json file
     *
     * @param  array $config
     * @return \Roots\Acorn\Assets\RelativePathManifest
     */
    public function createRelativeManifest(array $config): RelativePathManifest
    {
        $manifest = $this->getJsonManifest($config['manifest']);

        return new RelativePathManifest($config['path'], $config['uri'], $manifest);
    }

    /**
     * Opens a JSON manifest file from the local file system
     *
     * @param string $jsonManifest Path to .json file
     * @return array
     */
    protected function getJsonManifest(string $jsonManifest): array
    {
        $files = $this->app->get('files');

        return $files->exists($jsonManifest) ? \json_decode($files->get($jsonManifest), true) : [];
    }

    /**
     * Get the assets manifest configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig(string $name): array
    {
        return $this->app->get('config')["assets.manifests.{$name}"];
    }

    /**
     * Get the default manifest name.
     *
     * @return string
     */
    public function getDefaultManifest()
    {
        return $this->app->get('config')['assets.default'];
    }

    /**
     * Register a custom manifest creator Closure.
     *
     * @param  string    $strategy
     * @param  \Closure  $callback
     * @return $this
     */
    public function extend($strategy, Closure $callback)
    {
        $this->customCreators[$strategy] = $callback;

        return $this;
    }

    /**
     * Dynamically call the default manifest instance.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->manifest()->$method(...$parameters);
    }
}
