<?php

namespace Roots\Acorn\Session\Middleware;

use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession as BaseStartSession;

class StartSession extends BaseStartSession
{
    /**
     * Save the session data to storage.
     *
     * For WordPress routes, the session save is deferred until the WordPress
     * shutdown hook so that flash data remains available during template
     * rendering (which occurs after the middleware stack completes).
     *
     * @param  Request  $request
     * @return void
     */
    protected function saveSession($request)
    {
        if ($request->route()?->getName() === 'wordpress' && function_exists('add_action')) {
            add_action('shutdown', fn () => parent::saveSession($request), 0);

            return;
        }

        parent::saveSession($request);
    }
}
