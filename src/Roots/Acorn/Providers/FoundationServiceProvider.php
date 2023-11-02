<?php

namespace Roots\Acorn\Providers;

use Illuminate\Foundation\Providers\FormRequestServiceProvider;
use Illuminate\Foundation\Providers\FoundationServiceProvider as BaseFoundationServiceProvider;

class FoundationServiceProvider extends BaseFoundationServiceProvider
{
    /**
     * The provider class names.
     *
     * @var string[]
     */
    protected $providers = [
        FormRequestServiceProvider::class,
    ];

    /**
     * The singletons to register into the container.
     *
     * @var array
     */
    public $singletons = [];
}
