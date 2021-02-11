<?php

namespace Roots\Acorn;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class LazyLoader
{
    /**
     * List of available providers.
     *
     * @var string[]
     */
    protected $providers = [];

    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->defaultProviders();
    }

    /**
     * Registers an available provider and its bindings.
     *
     * @param string $provider
     * @param array $bindings
     * @return void
     * @throws InvalidArgumentException
     */
    public function registerProvider(string $provider, array $bindings)
    {
        if (! in_array(ServiceProvider::class, class_parents($provider) ?? [])) {
            throw new InvalidArgumentException(
                sprintf('First parameter must be class name of type [%s]', ServiceProvider::class)
            );
        }

        foreach ($bindings as $binding) {
            $this->providers[$binding] = $provider;
        }
    }

    /**
     * Get the provider for the specified binding.
     *
     * @param string $binding
     * @return string|null
     */
    public function getProvider($binding)
    {
        return $this->providers[$binding] ?? null;
    }

    /**
     * Register default providers.
     *
     * @return void
     */
    protected function defaultProviders()
    {
        $providers = [
            \Illuminate\Cache\CacheServiceProvider::class => [
                'cache',
                'cache.psr6',
                'cache.store',
                'memcached.connector',
            ],
            \Roots\Acorn\Filesystem\FilesystemServiceProvider::class => [
                'files',
                'filesystem',
                'filesystem.cloud',
                'filesystem.disk',
            ],
        ];

        foreach ($providers as $provider => $bindings) {
            $this->registerProvider($provider, $bindings);
        }
    }
}
