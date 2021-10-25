<?php

namespace Roots\Acorn\Tests\Test\Stubs;

use Illuminate\Support\ServiceProvider as ServiceProviderBase;

class BootableServiceProvider extends ServiceProviderBase
{
    public function boot()
    {
        // ...
    }
}
