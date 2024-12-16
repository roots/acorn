<?php

namespace Roots\Acorn\Tests\Test\Concerns;

use Roots\Acorn\Application;
use Illuminate\Support\Facades\Route;

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
