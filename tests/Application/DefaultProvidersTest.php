<?php

use Roots\Acorn\Tests\Test\Stubs\BootableServiceProvider;
use Roots\Acorn\Tests\Test\TestCase;

uses(TestCase::class);

it('dedupes registered providers', function () {
    $providers = \Roots\Acorn\ServiceProvider::defaultProviders()->merge([
        BootableServiceProvider::class,
        BootableServiceProvider::class,
    ]);

    expect($providers->toArray())->toBe(array_unique($providers->toArray()));
});

it('replaces default providers', function () {
    $providers = \Roots\Acorn\ServiceProvider::defaultProviders()->replace([
        \Roots\Acorn\Providers\AcornServiceProvider::class => BootableServiceProvider::class,
    ]);

    expect($providers->toArray())
        ->toContain(BootableServiceProvider::class)
        ->not()
        ->toContain(\Roots\Acorn\Providers\AcornServiceProvider::class);
});
