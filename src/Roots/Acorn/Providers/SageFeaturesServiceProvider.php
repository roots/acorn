<?php

namespace Roots\Acorn\Providers;

use Illuminate\Support\AggregateServiceProvider;

class SageFeaturesServiceProvider extends AggregateServiceProvider
{
    protected $providers = [
        \Roots\Acorn\Assets\AssetsServiceProvider::class,
        \Roots\Acorn\View\ViewServiceProvider::class,
        \Roots\Acorn\Sage\SageServiceProvider::class,
    ];
}
