<?php

use Roots\Acorn\Providers\AcornServiceProvider;
use Roots\Acorn\ServiceProvider;
use Roots\Acorn\Tests\Test\Stubs\BootableServiceProvider;
use Roots\Acorn\Tests\Test\TestCase;

uses(TestCase::class);

it('dedupes registered providers', function () {
    $providers = ServiceProvider::defaultProviders()->merge([
        BootableServiceProvider::class,
        BootableServiceProvider::class,
    ]);

    expect($providers->toArray())->toBe(array_unique($providers->toArray()));
});

it('replaces default providers', function () {
    $providers = ServiceProvider::defaultProviders()->replace([
        AcornServiceProvider::class => BootableServiceProvider::class,
    ]);

    expect($providers->toArray())
        ->toContain(BootableServiceProvider::class)
        ->not()
        ->toContain(AcornServiceProvider::class);
});
