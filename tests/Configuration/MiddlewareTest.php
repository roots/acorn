<?php

use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Roots\Acorn\Configuration\Middleware;
use Roots\Acorn\Tests\Test\TestCase;

uses(TestCase::class);

it('replaces global middleware with multiple middleware', function () {
    $middleware = (new Middleware())->replace(HandleCors::class, ['first', 'second']);

    expect($middleware->getGlobalMiddleware())
        ->each->toBeString()->and($middleware->getGlobalMiddleware())->toContain('first', 'second')
        ->not->toContain(HandleCors::class);
});

it('inserts global middleware before and after existing middleware', function () {
    $global = (new Middleware())
        ->insertBefore(HandleCors::class, 'before')
        ->insertAfter(HandleCors::class, 'after')
        ->getGlobalMiddleware();

    expect($global)->each->toBeString();

    $cors = array_search(HandleCors::class, $global, strict: true);

    expect($global[$cors - 1])
        ->toBe('before')
        ->and($global[$cors + 1])
        ->toBe('after')
        ->and($global[$cors - 2])
        ->toBe(TrustProxies::class);
});

it('inserts group middleware before and after existing middleware', function () {
    $groups = (new Middleware())
        ->insertBeforeInGroup('web', SubstituteBindings::class, 'before')
        ->insertAfterInGroup('wordpress', SubstituteBindings::class, ['after', 'after-again'])
        ->getMiddlewareGroups();

    expect($groups['web'])->each->toBeString()->and($groups['wordpress'])->each->toBeString();

    $web = array_search(SubstituteBindings::class, $groups['web'], strict: true);
    $wordpress = array_search(SubstituteBindings::class, $groups['wordpress'], strict: true);

    expect($groups['web'][$web - 1])
        ->toBe('before')
        ->and($groups['web'][$web - 2])
        ->toBe(ShareErrorsFromSession::class)
        ->and(array_slice($groups['wordpress'], $wordpress + 1, 2))
        ->toBe(['after', 'after-again']);
});
