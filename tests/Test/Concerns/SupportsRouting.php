<?php

namespace Roots\Acorn\Tests\Test\Concerns;

use Illuminate\Support\Facades\Route;
use Roots\Acorn\Application;

trait SupportsRouting
{
    protected function setUpRouting()
    {
        // The application will be booted by the mu-plugin
        $this->app = Application::getInstance();

        // Set up facades
        Route::setFacadeApplication($this->app);
    }
}
