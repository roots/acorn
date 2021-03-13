<?php

use Roots\Acorn\Assets\Asset\Asset;
use Roots\Acorn\Assets\Asset\JsonAsset;
use Roots\Acorn\Assets\Asset\PhpAsset;
use Roots\Acorn\Assets\Asset\SvgAsset;
use Roots\Acorn\Assets\Manifest;
use Roots\Acorn\Tests\TestCase;

use function Spatie\Snapshots\assertMatchesSnapshot;

uses(TestCase::class);

beforeEach(function () {
    $manifest = json_decode(file_get_contents($this->fixture('asset_types/public/manifest.json')), JSON_OBJECT_AS_ARRAY);
    $this->assets = new Manifest($this->fixture('asset_types'), 'https://k.jo', $manifest);
});

it('can create an asset', fn () =>
    expect($this->assets->asset('apray.ext'))->toBeInstanceOf(Asset::class)
);

it('can create a json asset', fn () =>
    expect($this->assets->asset('kjo.json'))->toBeInstanceOf(JsonAsset::class)
);

it('can create a php asset', fn () =>
    expect($this->assets->asset('bnif.php'))->toBeInstanceOf(PhpAsset::class)
);

it('can create a svg asset', fn () =>
    expect($this->assets->asset('bdubs.svg'))->toBeInstanceOf(SvgAsset::class)
);

it('can create a data URL', fn () =>
    assertMatchesSnapshot($this->assets->asset('bdubs.svg')->dataUrl())
);

it('can create a base64 data URL', fn () =>
    assertMatchesSnapshot($this->assets->asset('apray.ext')->dataUrl())
);

it('can decode json', fn () =>
    assertMatchesSnapshot($this->assets->asset('kjo.json')->decode())
);

it('can convert json to array', fn () =>
    assertMatchesSnapshot($this->assets->asset('kjo.json')->toArray())
);

it('can re-encode json', fn () =>
    assertMatchesSnapshot($this->assets->asset('kjo.json')->toJson())
);

it('can load a php asset', fn () =>
    assertMatchesSnapshot($this->assets->asset('bnif.php')->load())
);
