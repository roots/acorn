<?php

namespace Roots\Acorn\Http;

use Illuminate\Foundation\Bootstrap\BootProviders;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Roots\Acorn\Bootstrap\HandleExceptions;
use Roots\Acorn\Bootstrap\LoadConfiguration;
use Roots\Acorn\Bootstrap\LoadEnvironmentVariables;
use Roots\Acorn\Bootstrap\RegisterFacades;

class Kernel extends HttpKernel
{
    /**
     * The bootstrap classes for the application.
     *
     * @var string[]
     */
    protected $bootstrappers = [
        LoadEnvironmentVariables::class,
        LoadConfiguration::class,
        HandleExceptions::class,
        RegisterFacades::class,
        RegisterProviders::class,
        BootProviders::class,
    ];
}
