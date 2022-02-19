<?php

namespace Roots;

use Roots\Acorn\Assets\Bundle;
use Roots\Acorn\Assets\Contracts\Asset;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Roots\Acorn\Bootloader;

/**
 * Get asset from manifest
 *
 * @param  string $asset
 * @param  string $manifest
 * @return Asset
 */
function asset(string $asset, ?string $manifest = null): Asset
{
    if (! $manifest) {
        return \app('assets.manifest')->asset($asset);
    }

    return \app('assets')->manifest($manifest)->asset($asset);
}

/**
 * Get bundle from manifest
 *
 * @param  string $bundle
 * @param  string $manifest
 * @return Bundle
 */
function bundle(string $bundle, ?string $manifest = null): Bundle
{
    if (! $manifest) {
        return \app('assets.manifest')->bundle($bundle);
    }

    return \app('assets')->manifest($manifest)->bundle($bundle);
}

/**
 * Initialize the Acorn bootloader.
 *
 * @param  callable|null $callback
 * @return void
 */
function bootloader($callback = null)
{
    $bootloader = Bootloader::getInstance();

    if (is_callable($callback)) {
        $bootloader->call($callback);
    }
}

/**
 * Get the evaluated view contents for the given view or file.
 *
 * @param  string|null  $view
 * @param  \Illuminate\Contracts\Support\Arrayable|array  $data
 * @param  array  $mergeData
 * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
 *
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/8.x/src/Illuminate/Foundation/helpers.php
 */
function view($view = null, $data = [], $mergeData = [])
{
    $factory = \app(ViewFactory::class);

    if (func_num_args() === 0) {
        return $factory;
    }

    return $factory->exists($view)
        ? $factory->make($view, $data, $mergeData)
        : $factory->file($view, $data, $mergeData);
}


/**
 * @deprecated
 */
function app(...$args)
{
    return \app(...$args);
}

/**
 * @deprecated
 */
function app_path(...$args)
{
    return \app_path(...$args);
}

/**
 * @deprecated
 */
function base_path(...$args)
{
    return \base_path(...$args);
}

/**
 * @deprecated
 */
function bcrypt(...$args)
{
    return \bcrypt(...$args);
}

/**
 * @deprecated
 */
function broadcast(...$args)
{
    return \broadcast(...$args);
}

/**
 * @deprecated
 */
function cache(...$args)
{
    return \cache(...$args);
}

/**
 * @deprecated
 */
function config(...$args)
{
    return \config(...$args);
}

/**
 * @deprecated
 */
function config_path(...$args)
{
    return \config_path(...$args);
}

/**
 * @deprecated
 */
function database_path(...$args)
{
    return \database_path(...$args);
}

/**
 * @deprecated
 */
function decrypt(...$args)
{
    return \decrypt(...$args);
}

/**
 * @deprecated
 */
function encrypt(...$args)
{
    return \encrypt(...$args);
}

/**
 * @deprecated
 */
function info(...$args)
{
    return \info(...$args);
}

/**
 * @deprecated
 */
function logger(...$args)
{
    return \logger(...$args);
}

/**
 * @deprecated
 */
function logs(...$args)
{
    return \logs(...$args);
}

/**
 * @deprecated
 */
function now(...$args)
{
    return \now(...$args);
}

/**
 * @deprecated
 */
function public_path(...$args)
{
    return \public_path(...$args);
}

/**
 * @deprecated
 */
function report(...$args)
{
    return \report(...$args);
}

/**
 * @deprecated
 */
function rescue(...$args)
{
    return \rescue(...$args);
}

/**
 * @deprecated
 */
function resolve(...$args)
{
    return \resolve(...$args);
}

/**
 * @deprecated
 */
function resource_path(...$args)
{
    return \resource_path(...$args);
}

/**
 * @deprecated
 */
function storage_path(...$args)
{
    return \storage_path(...$args);
}

/**
 * @deprecated
 */
function today(...$args)
{
    return \today(...$args);
}
