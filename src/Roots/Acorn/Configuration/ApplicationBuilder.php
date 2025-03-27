<?php

namespace Roots\Acorn\Configuration;

use Closure;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
use Illuminate\Foundation\Configuration\ApplicationBuilder as FoundationApplicationBuilder;
use Roots\Acorn\Application;
use Roots\Acorn\Configuration\Concerns\Paths;

class ApplicationBuilder extends FoundationApplicationBuilder
{
    use Paths;

    /**
     * The application builder configuration.
     */
    protected array $config = [];

    /**
     * Register the standard kernel classes for the application.
     *
     * @return $this
     */
    public function withKernels()
    {
        $this->app->singleton(
            \Illuminate\Contracts\Http\Kernel::class,
            \Roots\Acorn\Http\Kernel::class
        );

        $this->app->singleton(
            \Illuminate\Contracts\Console\Kernel::class,
            \Roots\Acorn\Console\Kernel::class
        );

        return $this;
    }

    /**
     * Register and configure the application's exception handler.
     *
     * @return $this
     */
    public function withExceptions(?callable $using = null)
    {
        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \Roots\Acorn\Exceptions\Handler::class,
        );

        $using ??= fn () => true;

        $this->app->afterResolving(
            \Roots\Acorn\Exceptions\Handler::class,
            fn ($handler) => $using(new Exceptions($handler)),
        );

        return $this;
    }

    /**
     * Register the routing services for the application.
     *
     * @return $this
     */
    public function withRouting(?Closure $using = null,
        array|string|null $web = null,
        array|string|null $api = null,
        ?string $commands = null,
        ?string $channels = null,
        ?string $pages = null,
        ?string $health = null,
        string $apiPrefix = 'api',
        ?callable $then = null,
        bool $wordpress = false)
    {
        if (! $web && file_exists($path = base_path('routes/web.php'))) {
            $web = $path;
        }

        parent::withRouting($using, $web, $api, $commands, $channels, $pages, $health, $apiPrefix, $then);

        if ($wordpress) {
            $this->app->handleWordPressRequests();
        }

        $this->config['routing'] = [
            'web' => $web,
            'api' => $api,
            'commands' => $commands,
            'channels' => $channels,
            'pages' => $pages,
            'health' => $health,
            'apiPrefix' => $apiPrefix,
            'wordpress' => $wordpress,
        ];

        return $this;
    }

    /**
     * Register the global middleware, middleware groups, and middleware aliases for the application.
     *
     * @return $this
     */
    public function withMiddleware(?callable $callback = null)
    {
        $this->app->afterResolving(HttpKernel::class, function ($kernel) use ($callback) {
            $middleware = new Middleware;

            if (! is_null($callback)) {
                $callback($middleware);
            }

            $this->pageMiddleware = $middleware->getPageMiddleware();

            $kernel->setGlobalMiddleware($middleware->getGlobalMiddleware());
            $kernel->setMiddlewareGroups($middleware->getMiddlewareGroups());
            $kernel->setMiddlewareAliases($middleware->getMiddlewareAliases());

            if ($priorities = $middleware->getMiddlewarePriority()) {
                $kernel->setMiddlewarePriority($priorities);
            }
        });

        return $this;
    }

    /**
     * Register additional service providers.
     *
     * @return $this
     */
    public function withProviders(array $providers = [], bool $withBootstrapProviders = true)
    {
        RegisterProviders::merge(
            $providers,
            $withBootstrapProviders
                ? $this->app->getBootstrapProvidersPath()
                : null
        );

        $this->config['providers'] = [
            ...$this->config['providers'] ?? [],
            ...$providers,
        ];

        return $this;
    }

    /**
     * Get the application instance.
     *
     * @return \Roots\Acorn\Application
     */
    public function create()
    {
        return $this->app;
    }

    /**
     * Boot the application.
     *
     * @return \Roots\Acorn\Application
     */
    public function boot()
    {
        return $this->app->bootAcorn($this->config);
    }
}
