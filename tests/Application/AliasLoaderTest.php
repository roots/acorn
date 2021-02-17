<?php

use Roots\Acorn\AliasLoader;

it('should load global function aliases', function () {
    $loader = AliasLoader::getInstance([]);

    expect(function_exists('app'))->toBeFalse();
    expect(function_exists('asset'))->toBeFalse();
    expect(function_exists('config'))->toBeFalse();
    expect(function_exists('view'))->toBeFalse();

    $loader->register();

    expect(function_exists('app'))->toBeTrue();
    expect(function_exists('asset'))->toBeTrue();
    expect(function_exists('config'))->toBeTrue();
    expect(function_exists('view'))->toBeTrue();
});
