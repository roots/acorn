<?php

namespace Roots\Acorn\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * {@inheritdoc}
     */
    public function handle($request, Closure $next)
    {
        $key = config('app.key');

        if (blank($key)) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
