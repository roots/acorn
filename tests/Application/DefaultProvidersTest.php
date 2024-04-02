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
