<?php

namespace Roots\Acorn\Concerns;

use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Support\Composer;

trait Bindings
{
    /** @var string[] The service binding methods that have been executed.*/
    protected $ranServiceBinders = [];

    /** @var array The available container bindings and their respective load methods. */
    public $availableBindings = [];

    /**
     * Register available core container bindings and their respective load methods.
     *
     * @return void
     */
    public function registerContainerBindings()
    {
        // phpcs:disable
        foreach([
            'registerAuthBindings' => ['auth', 'auth.driver', \Illuminate\Auth\AuthManager::class, \Illuminate\Contracts\Auth\Guard::class, \Illuminate\Contracts\Auth\Access\Gate::class],
            'registerBroadcastingBindings' => [\Illuminate\Contracts\Broadcasting\Broadcaster::class, \Illuminate\Contracts\Broadcasting\Factory::class],
            'registerBusBindings' => [\Illuminate\Contracts\Bus\Dispatcher::class],
            'registerCacheBindings' => ['cache', 'cache.store', \Illuminate\Contracts\Cache\Factory::class, \Illuminate\Contracts\Cache\Repository::class],
            'registerComposerBindings' => ['composer'],
            'registerConfigBindings' => ['config'],
            'registerConsoleBindings' => ['artisan', 'console', \Acorn\Console\Kernel::class, \Illuminate\Contracts\Console\Kernel::class],
            'registerDatabaseBindings' => ['db', \Illuminate\Database\Eloquent\Factory::class],
            'registerEncrypterBindings' => ['encrypter', \Illuminate\Contracts\Encryption\Encrypter::class],
            'registerEventBindings' => ['events', \Illuminate\Contracts\Events\Dispatcher::class],
            'registerFilesBindings' => ['files', \Roots\Acorn\Filesystem\Filesystem::class, \Illuminate\Filesystem\Filesystem::class],
            'registerFilesystemBindings' => ['filesystem', 'filesystem.cloud', 'filesystem.disk', \Illuminate\Contracts\Filesystem\Factory::class, \Illuminate\Contracts\Filesystem\Cloud::class, \Illuminate\Contracts\Filesystem\Filesystem::class],
            'registerHashBindings' => ['hash', \Illuminate\Contracts\Hashing\Hasher::class],
            'registerLogBindings' => ['log', \Psr\Log\LoggerInterface::class],
            'registerQueueBindings' => ['queue', 'queue.connection', \Illuminate\Contracts\Queue\Factory::class, \Illuminate\Contracts\Queue\Queue::class],
            'registerRouterBindings' => ['router'],
            'registerPsrRequestBindings' => [\Psr\Http\Message\ServerRequestInterface::class, \Psr\Http\Message\ResponseInterface::class],
            'registerTranslationBindings' => ['translator'],
            'registerUrlGeneratorBindings' => ['url'],
            'registerValidatorBindings' => ['validator', \Illuminate\Contracts\Validation\Factory::class],
            'registerViewBindings' => ['view', \Illuminate\Contracts\View\Factory::class],
        ] as $method => $abstracts) {
            foreach($abstracts as $abstract) {
                $this->availableBindings[$abstract] = $method;
            }
        }
        // phpcs:enable
    }

    /**
     * Resolve the given type from a binding.
     *
     * @param string $abstract
     * @return void
     */
    public function makeWithBinding($abstract)
    {
        if (
            array_key_exists($abstract, $this->availableBindings)
            && ! array_key_exists($this->availableBindings[$abstract], $this->ranServiceBinders)
        ) {
            $this->{$method = $this->availableBindings[$abstract]}();
            $this->ranServiceBinders[$method] = true;
        }
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerAuthBindings()
    {
        $this->singleton('auth', function () {
            return $this->loadComponent('auth', \Illuminate\Auth\AuthServiceProvider::class, 'auth');
        });

        $this->singleton('auth.driver', function () {
            return $this->loadComponent('auth', \Illuminate\Auth\AuthServiceProvider::class, 'auth.driver');
        });

        $this->singleton(\Illuminate\Contracts\Auth\Access\Gate::class, function () {
            return $this->loadComponent(
                'auth',
                \Illuminate\Auth\AuthServiceProvider::class,
                \Illuminate\Contracts\Auth\Access\Gate::class
            );
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerBroadcastingBindings()
    {
        $this->singleton(Illuminate\Contracts\Broadcasting\Factory::class, function () {
            return $this->loadComponent(
                'broadcasting',
                \Illuminate\Broadcasting\BroadcastServiceProvider::class,
                \Illuminate\Contracts\Broadcasting\Factory::class
            );
        });

        $this->singleton(Illuminate\Contracts\Broadcasting\Broadcaster::class, function () {
            return $this->loadComponent(
                'broadcasting',
                \Illuminate\Broadcasting\BroadcastServiceProvider::class,
                \Illuminate\Contracts\Broadcasting\Broadcaster::class
            );
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerBusBindings()
    {
        $this->singleton(\Illuminate\Contracts\Bus\Dispatcher::class, function () {
            $this->register(\Illuminate\Bus\BusServiceProvider::class);
            return $this->make(\Illuminate\Contracts\Bus\Dispatcher::class);
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerCacheBindings()
    {
        $this->app->singleton('cache', function ($app) {
            $this->configure('cache');
            return new \Illuminate\Cache\CacheManager($app);
        });

        $this->app->singleton('cache.store', function ($app) {
            return $app['cache']->driver();
        });

        $this->app->singleton('memcached.connector', function () {
            return new \Illuminate\Cache\MemcachedConnector();
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerComposerBindings()
    {
        $this->singleton('composer', function ($app) {
            return new Composer($app->make('files'), $this->basePath());
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerConfigBindings()
    {
        $this->singleton('config', function () {
            return new ConfigRepository();
        });
    }

    /**
     * Registeer container bindings for the application.
     *
     * @return void
     */
    protected function registerConsoleBindings()
    {
        $this->singleton('console', \Roots\Acorn\Console\Kernel::class);
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerDatabaseBindings()
    {
        $this->singleton('db', function () {
            return $this->loadComponent('database', [
                \Illuminate\Database\DatabaseServiceProvider::class,
                \Illuminate\Pagination\PaginationServiceProvider::class,
            ], 'db');
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerEncrypterBindings()
    {
        $this->singleton('encrypter', function () {
            return $this->loadComponent('app', \Illuminate\Encryption\EncryptionServiceProvider::class, 'encrypter');
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerEventBindings()
    {
        $this->singleton('events', function () {
            $this->register(\Illuminate\Events\EventServiceProvider::class);
            return $this->make('events');
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerFilesBindings()
    {
        $this->singleton('files', function () {
            return new \Roots\Acorn\Filesystem\Filesystem();
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerFilesystemBindings()
    {
        $this->singleton('filesystem', function () {
            return $this->loadComponent(
                'filesystems',
                \Roots\Acorn\Filesystem\FilesystemServiceProvider::class,
                'filesystem'
            );
        });

        $this->singleton('filesystem.disk', function () {
            return $this->loadComponent(
                'filesystems',
                \Roots\Acorn\Filesystem\FilesystemServiceProvider::class,
                'filesystem.disk'
            );
        });

        $this->singleton('filesystem.cloud', function () {
            return $this->loadComponent(
                'filesystems',
                \Roots\Acorn\Filesystem\FilesystemServiceProvider::class,
                'filesystem.cloud'
            );
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerHashBindings()
    {
        $this->singleton('hash', function () {
            $this->register(\Illuminate\Hashing\HashServiceProvider::class);
            return $this->make('hash');
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerLogBindings()
    {
        $this->singleton('log', function () {
            return $this->loadComponent('log', \Illuminate\Log\LogServiceProvider::class);
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerQueueBindings()
    {
        $this->singleton('queue', function () {
            return $this->loadComponent('queue', \Illuminate\Queue\QueueServiceProvider::class, 'queue');
        });
        $this->singleton('queue.connection', function () {
            return $this->loadComponent('queue', \Illuminate\Queue\QueueServiceProvider::class, 'queue.connection');
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerTranslationBindings()
    {
        $this->singleton('translator', function () {
            $this->configure('app');
            $this->instance('path.lang', $this->langPath());
            $this->register(\Illuminate\Translation\TranslationServiceProvider::class);
            return $this->make('translator');
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerUrlGeneratorBindings()
    {
        $this->singleton('url', function () {
            return new Routing\UrlGenerator($this);
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerValidatorBindings()
    {
        $this->singleton('validator', function () {
            $this->register(\Illuminate\Validation\ValidationServiceProvider::class);
            return $this->make('validator');
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerViewBindings()
    {
        $this->singleton('view', function () {
            return $this->loadComponent('view', \Illuminate\View\ViewServiceProvider::class);
        });
    }
}
