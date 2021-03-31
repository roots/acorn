<?php

namespace Roots\Acorn\Assets;

use Roots\Acorn\Assets\Asset\JsonAsset;
use Roots\Acorn\Assets\Asset\PhpAsset;
use Roots\Acorn\Assets\Asset\SvgAsset;
use Roots\Acorn\Assets\Asset\Asset;
use Roots\Acorn\Assets\Contracts\Asset as AssetContract;

class AssetFactory
{
    /**
     * Create Asset instance.
     *
     * @param  string $path Local path
     * @param  string $uri Remote URI
     * @param  string $type Asset type
     */
    public static function create(string $path, string $uri, ?string $type = null): AssetContract
    {
        if (! $type) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
        }

        if (method_exists(self::class, $method = 'create' . ucfirst(strtolower($type)) . 'Asset')) {
            return self::{$method}($path, $uri);
        }

        return self::createAsset($path, $uri);
    }

    /**
     * Convert an asset to another asset type.
     *
     * @param AssetContract $asset
     * @param string $type
     * @return AssetContract
     */
    public static function convert(AssetContract $asset, string $type): AssetContract
    {
        return self::create($asset->path(), $asset->uri(), $type);
    }

    /**
     * Create Asset instance.
     *
     * @param string $path
     * @param string $uri
     * @return Asset
     */
    protected static function createAsset(string $path, string $uri): Asset
    {
        return new Asset($path, $uri);
    }

    /**
     * Create JsonAsset instance.
     *
     * @param string $path
     * @param string $uri
     * @return JsonAsset
     */
    protected static function createJsonAsset(string $path, string $uri): JsonAsset
    {
        return new JsonAsset($path, $uri);
    }

    /**
     * Create PhpAsset instance.
     *
     * @param string $path
     * @param string $uri
     * @return PhpAsset
     */
    protected static function createPhpAsset(string $path, string $uri): PhpAsset
    {
        return new PhpAsset($path, $uri);
    }

    /**
     * Create SvgAsset instance.
     *
     * @param string $path
     * @param string $uri
     * @return SvgAsset
     */
    protected static function createSvgAsset(string $path, string $uri): SvgAsset
    {
        return new SvgAsset($path, $uri);
    }
}
