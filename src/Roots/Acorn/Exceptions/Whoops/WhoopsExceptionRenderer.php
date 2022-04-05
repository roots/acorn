<?php

namespace Roots\Acorn\Exceptions\Whoops;

use Illuminate\Foundation\Exceptions\Whoops\WhoopsExceptionRenderer as FoundationWhoopsExceptionRenderer;

class WhoopsExceptionRenderer extends FoundationWhoopsExceptionRenderer
{
    /**
     * Get the Whoops handler for the application.
     *
     * @return \Whoops\Handler\Handler
     */
    protected function whoopsHandler()
    {
        return (new WhoopsHandler())->forDebug();
    }
}
