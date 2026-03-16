<?php

namespace Roots\Acorn\Configuration;

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Configuration\Middleware as FoundationMiddleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Http\Middleware\TrustHosts;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Middleware\ValidatePostSize;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ThrottleRequestsWithRedis;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Roots\Acorn\Session\Middleware\StartSession;
use Spark\Http\Middleware\VerifyBillableIsSubscribed;

class Middleware extends FoundationMiddleware
{
    /**
     * Get the middleware aliases.
     *
     * @return array
     */
    public function getMiddlewareAliases()
    {
        return array_merge($this->defaultAliases(), $this->customAliases);
    }

    /**
     * Get the global middleware.
     *
     * @return array
     */
    public function getGlobalMiddleware()
    {
        $middleware = $this->global ?: array_values(array_filter([
            $this->trustHosts ? TrustHosts::class : null,
            TrustProxies::class,
            HandleCors::class,
            ValidatePostSize::class,
            ConvertEmptyStringsToNull::class,
        ]));

        $middleware = array_map(function ($middleware) {
            return isset($this->replacements[$middleware])
                ? $this->replacements[$middleware]
                : $middleware;
        }, $middleware);

        return array_values(array_filter(
            array_diff(
                array_unique(array_merge($this->prepends, $middleware, $this->appends)),
                $this->removals
            )
        ));
    }

    /**
     * Modify the middleware in the "wordpress" group.
     *
     * @return $this
     */
    public function wordpress(array|string $append = [], array|string $prepend = [], array|string $remove = [], array $replace = [])
    {
        return $this->modifyGroup('wordpress', $append, $prepend, $remove, $replace);
    }

    /**
     * Get the middleware groups.
     *
     * @return array
     */
    public function getMiddlewareGroups()
    {
        $middleware = [
            'web' => array_values(array_filter([
                // \Illuminate\Cookie\Middleware\EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                // \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
                SubstituteBindings::class,
                $this->authenticatedSessions ? 'auth.session' : null,
            ])),

            'api' => array_values(array_filter([
                $this->statefulApi ? EnsureFrontendRequestsAreStateful::class : null,
                $this->apiLimiter ? 'throttle:'.$this->apiLimiter : null,
                SubstituteBindings::class,
            ])),
        ];

        $middleware['wordpress'] = $middleware['web'];

        $middleware = array_merge($middleware, $this->groups);

        foreach ($middleware as $group => $groupedMiddleware) {
            foreach ($groupedMiddleware as $index => $groupMiddleware) {
                if (isset($this->groupReplacements[$group][$groupMiddleware])) {
                    $middleware[$group][$index] = $this->groupReplacements[$group][$groupMiddleware];
                }
            }
        }

        foreach ($this->groupRemovals as $group => $removals) {
            $middleware[$group] = array_values(array_filter(
                array_diff($middleware[$group] ?? [], $removals)
            ));
        }

        foreach ($this->groupPrepends as $group => $prepends) {
            $middleware[$group] = array_values(array_filter(
                array_unique(array_merge($prepends, $middleware[$group] ?? []))
            ));
        }

        foreach ($this->groupAppends as $group => $appends) {
            $middleware[$group] = array_values(array_filter(
                array_unique(array_merge($middleware[$group] ?? [], $appends))
            ));
        }

        return $middleware;
    }

    /**
     * Get the default middleware aliases.
     *
     * @return array
     */
    protected function defaultAliases()
    {
        $aliases = [
            // 'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            // 'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'auth.session' => AuthenticateSession::class,
            'cache.headers' => SetCacheHeaders::class,
            // 'can' => \Illuminate\Auth\Middleware\Authorize::class,
            // 'guest' => \Illuminate\Auth\Middleware\RedirectIfAuthenticated::class,
            // 'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'precognitive' => HandlePrecognitiveRequests::class,
            'signed' => ValidateSignature::class,
            'throttle' => $this->throttleWithRedis
                ? ThrottleRequestsWithRedis::class
                : ThrottleRequests::class,
            // 'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        ];

        if (class_exists(VerifyBillableIsSubscribed::class)) {
            $aliases['subscribed'] = VerifyBillableIsSubscribed::class;
        }

        return $aliases;
    }
}
