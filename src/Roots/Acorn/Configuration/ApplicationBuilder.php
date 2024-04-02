<?php

namespace Roots\Acorn\Configuration;

use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
use Illuminate\Foundation\Configuration\ApplicationBuilder as FoundationApplicationBuilder;
use Roots\Acorn\Application;

class ApplicationBuilder extends FoundationApplicationBuilder
{
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
     * Register and configure the application's paths.
     *
     * @return $this
     */
    public function withPaths(array $paths = [])
    {
        $this->app->usePaths($paths);

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
        return $this->app->bootAcorn();
    }
}
