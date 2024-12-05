<?php

namespace Roots\Acorn\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
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
