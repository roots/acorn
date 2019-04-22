<?php

namespace Roots;

use Illuminate\View\View;
use Roots\Acorn\Application;
use Roots\Acorn\Assets\Asset;
use Roots\Acorn\Assets\Manifest;

/**
 * Get the available container instance.
 *
 * @param  string $abstract
 * @param  array  $parameters
 * @return mixed|\Roots\Acorn\Application
 *
 * @copyright Taylor Otwell
 * @license   https://github.com/laravel/framework/blob/v5.6.25/LICENSE.md MIT
 * @link      https://github.com/laravel/framework/blob/v5.6.25/src/Illuminate/Foundation/helpers.php#L106-L120
 */
function app($abstract = null, array $parameters = [])
{
    if (is_null($abstract)) {
        return Application::getInstance();
    }

    return Application::getInstance()->make($abstract, $parameters);
}

/**
 * Get / set the specified configuration value.
 *
 * If an array is passed as the key, we will assume you want to set an array of values.
 *
 * @param  array|string $key
 * @param  mixed        $default
 * @return mixed|\Roots\Acorn\Config
 *
 * @copyright Taylor Otwell
 * @license   https://github.com/laravel/framework/blob/v5.6.25/LICENSE.md MIT
 * @link      https://github.com/laravel/framework/blob/v5.6.25/src/Illuminate/Foundation/helpers.php#L262-L282
 */
function config($key = null, $default = null)
{
    if (is_null($key)) {
        return app('config');
    }

    if (is_array($key)) {
        return app('config')->set($key);
    }

    return app('config')->get($key, $default);
}

/**
 * Get asset from manifest
 *
 * @param  string $asset Relative path of the asset before cache-busting
 * @param  \Roots\Acorn\Assets\Manifest $manifest
 * @return \Roots\Acorn\Assets\Asset
 */
function asset($asset, Manifest $manifest = null)
{
    if (! $manifest) {
        $manifest = app('assets.manifest');
    }

    /*
     * Massage the asset reference slightly to alleviate common issues
     */
    $asset = str_replace('\\', '/', $asset);
    $asset = ltrim($asset, '/');

    return new Asset($asset, $manifest);
}

/**
 * Get the evaluated view contents for the given view.
 *
 * @param  string $view      View name or file path
 * @param  array  $data
 * @param  array  $mergeData
 * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
 *
 * @copyright Taylor Otwell
 * @link      https://github.com/laravel/framework/blob/v5.6.25/src/Illuminate/Foundation/helpers.php#L983-L1000
 */
function view($view = null, $data = [], $mergeData = [])
{
    /** @var \Illuminate\Contracts\View\Factory $factory */
    $factory = app('view');
    if (func_num_args() === 0) {
        return $factory;
    }

    return $factory->exists($view)
        ? $factory->make($view, $data, $mergeData)
        : $factory->file($view, $data, $mergeData);
}

/**
 * Acorn bootloader
 */
function bootloader()
{
    static $booted;
    if ($booted) {
        return;
    }
    $booted = true;

    $bootstrap = [
        \Roots\Acorn\Bootstrap\LoadConfiguration::class,
        \Roots\Acorn\Bootstrap\LoadBindings::class,
        \Roots\Acorn\Bootstrap\RegisterGlobals::class,
        \Roots\Acorn\Bootstrap\SageFeatures::class,
    ];
    $application_basepath = dirname(locate_template('config') ?: __DIR__);

    if (get_theme_support('sage')) {
        $application_basepath = dirname(get_theme_file_path());
    }
    $application_basepath = apply_filters(
        'acorn/basepath',
        \defined('ACORN_BASEPATH')
            ? ACORN_BASEPATH
            : env('ACORN_BASEPATH', $application_basepath)
    );

    $app = new \Roots\Acorn\Application($application_basepath);

    $app->bootstrapWith(apply_filters('acorn/bootstrap', $bootstrap));

    if ($app->isBooted()) {
        return;
    }

    $app->boot();
}
