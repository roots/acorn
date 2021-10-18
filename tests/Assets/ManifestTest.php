<?php

use Roots\Acorn\Assets\Contracts\Asset as AssetContract;
use Roots\Acorn\Assets\Contracts\Bundle as BundleContract;
use Roots\Acorn\Assets\Manifest;
use Roots\Acorn\Tests\Test\TestCase;

uses(TestCase::class);

it('can get an asset', function () {
    $manifest = new Manifest(
        $this->fixture('bud_single_runtime'),
        'https://k.jo',
        json_decode(file_get_contents($this->fixture('bud_single_runtime/public/manifest.json')), JSON_OBJECT_AS_ARRAY)
    );

    expect($manifest->asset('app.js'))->toBeInstanceOf(AssetContract::class);
});

it('can get a bundle', function () {
    $manifest = new Manifest(
        $this->fixture('bud_single_runtime'),
        'https://k.jo',
        [],
        json_decode(file_get_contents($this->fixture('bud_single_runtime/public/entrypoints.json')), JSON_OBJECT_AS_ARRAY)
    );

    expect($manifest->bundle('app'))->toBeInstanceOf(BundleContract::class);
});
