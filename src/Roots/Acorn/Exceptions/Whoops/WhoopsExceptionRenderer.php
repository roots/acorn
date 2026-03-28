<?php

namespace Roots\Acorn\Exceptions\Whoops;

use Illuminate\Foundation\Exceptions\Whoops\WhoopsExceptionRenderer as FoundationWhoopsExceptionRenderer;
use Whoops\Handler\Handler;

class WhoopsExceptionRenderer extends FoundationWhoopsExceptionRenderer
{
    /**
     * Get the Whoops handler for the application.
     *
     * @return Handler
     */
    protected function whoopsHandler()
    {
        return (new WhoopsHandler())->forDebug();
    }
}
