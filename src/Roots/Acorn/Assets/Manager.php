<?php

namespace Roots\Acorn\Assets;

use InvalidArgumentException;
use Roots\Acorn\Assets\Contracts\Manifest as ManifestContract;
use Roots\Acorn\Assets\Contracts\ManifestNotFoundException;
use Roots\Acorn\Assets\Middleware\LaravelMixMiddleware;
use Roots\Acorn\Assets\Middleware\RootsBudMiddleware;

/**
 * Manage assets manifests
 *
 * @see \Illuminate\Support\Manager
 * @link https://github.com/illuminate/support/blob/8.x/Manager.php
 */
class Manager
{
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
     * Manifest middleware.
     *
     * @var string[]
     */
    protected $middleware = [
        RootsBudMiddleware::class,
        LaravelMixMiddleware::class,
    ];

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

        $config = $this->pipeline($config);

        $path = $config['path'];
        $url = $config['url'];
        $assets = isset($config['assets']) ? $this->getJsonManifest($config['assets']) : [];
        $bundles = isset($config['bundles']) ? $this->getJsonManifest($config['bundles']) : [];

        return new Manifest($path, $url, $assets, $bundles);
    }

    /**
     * Manifest config pipeline.
     *
     * @param array $config
     * @return array
     */
    protected function pipeline(array $config): array
    {
        return array_reduce($this->middleware, function (array $config, $middleware): array {
            if (is_string($middleware) && class_exists($middleware)) {
                $middleware = new $middleware();
            }

            return is_callable($middleware) ? $middleware($config) : $middleware->handle($config);
        }, $config);
    }

    /**
     * Opens a JSON manifest file from the local file system
     *
     * @param string $jsonManifest Path to .json file
     * @return array
     */
    protected function getJsonManifest(string $jsonManifest): array
    {
        if (! file_exists($jsonManifest)) {
            throw new ManifestNotFoundException("The manifest [{$jsonManifest}] cannot be found.");
        }

        return json_decode(file_get_contents($jsonManifest), true) ?? [];
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
