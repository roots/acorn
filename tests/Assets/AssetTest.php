<?php

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Roots\Acorn\Assets\Asset\Asset;
use Roots\Acorn\Assets\Asset\JsonAsset;
use Roots\Acorn\Assets\Asset\PhpAsset;
use Roots\Acorn\Assets\Asset\SvgAsset;
use Roots\Acorn\Assets\Manifest;
use Roots\Acorn\Tests\Test\TestCase;

use function Roots\Acorn\Tests\temp;
use function Spatie\Snapshots\assertMatchesSnapshot;

uses(TestCase::class);

beforeEach(function () {
    $manifest = json_decode(file_get_contents($this->fixture('asset_types/public/manifest.json')), JSON_OBJECT_AS_ARRAY);
    $this->assets = new Manifest($this->fixture('asset_types'), 'https://k.jo', $manifest);
});

it('can create an asset', function () {
    expect($this->assets->asset('apray.ext'))->toBeInstanceOf(Asset::class);
});

it('can create a json asset', function () {
    expect($this->assets->asset('kjo.json'))->toBeInstanceOf(JsonAsset::class);
});

it('can create a php asset', function () {
    expect($this->assets->asset('bnif.php'))->toBeInstanceOf(PhpAsset::class);
});

it('can create a svg asset', function () {
    expect($this->assets->asset('bdubs.svg'))->toBeInstanceOf(SvgAsset::class);
});

it('can create a data URL', function () {
    assertMatchesSnapshot($this->assets->asset('bdubs.svg')->dataUrl());
});

it('can create a base64 data URL', function () {
    assertMatchesSnapshot($this->assets->asset('apray.ext')->dataUrl());
});

it('can decode json', function () {
    assertMatchesSnapshot($this->assets->asset('kjo.json')->decode());
});

it('can convert json to array', function () {
    assertMatchesSnapshot($this->assets->asset('kjo.json')->toArray());
});

it('can re-encode json', function () {
    assertMatchesSnapshot($this->assets->asset('kjo.json')->toJson());
});

it('can include a php asset', function () {
    assertMatchesSnapshot($this->assets->asset('bnif.php')->load());
});

it('can include_once a php asset', function () {
    assertMatchesSnapshot($this->assets->asset('bnif.php')->load(false, true));
});

it('can require a php asset', function () {
    assertMatchesSnapshot($this->assets->asset('bnif.php')->load(true));
});

it('can require_once a php asset', function () {
    assertMatchesSnapshot($this->assets->asset('bnif.php')->load(true, true));
});

it('can fail to load a php asset', function () {
    (new PhpAsset(temp('does/not/exist.php'), 'https://kjo.kjo/'))->load();
})->throws(FileNotFoundException::class);
