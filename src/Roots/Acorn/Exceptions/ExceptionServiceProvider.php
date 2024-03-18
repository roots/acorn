<?php

namespace Roots\Acorn\Exceptions;

use Illuminate\Support\ServiceProvider;
use Roots\Acorn\Exceptions\Solutions\ManifestSolutionProvider;
use Roots\Acorn\Exceptions\Solutions\SkipProviderSolutionProvider;
use Spatie\Ignition\Contracts\SolutionProviderRepository;

class ExceptionServiceProvider extends ServiceProvider
{
    /**
     * The solution providers that should be registered.
     */
    protected array $solutions = [
        ManifestSolutionProvider::class,
        SkipProviderSolutionProvider::class,
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (! class_exists('Spatie\Ignition\Ignition')) {
            return;
        }

        $this->registerSolutions();
    }

    /**
     * Register the exception solutions.
     */
    protected function registerSolutions()
    {
        $this->app->make(SolutionProviderRepository::class)->registerSolutionProviders($this->solutions);
    }
}
