<?php

namespace Roots;

use Illuminate\Contracts\Broadcasting\Factory as BroadcastFactory;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Facades\Date;
use Roots\Acorn\Application;

/**
 * Get the available container instance.
 *
 * @param  string|null  $abstract
 * @param  array  $parameters
 * @return mixed|\Roots\Acorn\Application
 *
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/7.x/src/Illuminate/Foundation/helpers.php
 */
function app($abstract = null, array $parameters = [])
{
    if (is_null($abstract)) {
        return Application::getInstance();
    }

    return Application::getInstance()->make($abstract, $parameters);
}

/**
 * Get the path to the application folder.
 *
 * @param  string  $path
 * @return string
 *
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/7.x/src/Illuminate/Foundation/helpers.php
 */
function app_path($path = '')
{
    return app()->path($path);
}

/**
 * Get asset from manifest
 *
 * @param  string $key
 * @param  string $manifest
 * @return \Roots\Acorn\Assets\Asset
 */
function asset($key, $manifest = null)
{
    return app('assets')->manifest($manifest)->get($key);
}

/**
 * Get the path to the base of the install.
 *
 * @param  string  $path
 * @return string
 *
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/7.x/src/Illuminate/Foundation/helpers.php
 */
function base_path($path = '')
{
    return app()->basePath($path);
}

/**
 * Hash the given value against the bcrypt algorithm.
 *
 * @param  string  $value
 * @param  array  $options
 * @return string
 *
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/7.x/src/Illuminate/Foundation/helpers.php
 */
function bcrypt($value, $options = [])
{
    return app('hash')->driver('bcrypt')->make($value, $options);
}

/**
 * Begin broadcasting an event.
 *
 * @param  mixed|null  $event
 * @return \Illuminate\Broadcasting\PendingBroadcast
 *
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/7.x/src/Illuminate/Foundation/helpers.php
 */
function broadcast($event = null)
{
    return app(BroadcastFactory::class)->event($event);
}

/**
 * Get / set the specified cache value.
 *
 * If an array is passed, we'll assume you want to put to the cache.
 *
 * @param  dynamic  key|key,default|data,expiration|null
 * @return mixed|\Illuminate\Cache\CacheManager
 *
 * @throws \Exception
 *
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/7.x/src/Illuminate/Foundation/helpers.php
 */
function cache()
{
    $arguments = func_get_args();

    if (empty($arguments)) {
        return app('cache');
    }

    if (is_string($arguments[0])) {
        return app('cache')->get(...$arguments);
    }

    if (! is_array($arguments[0])) {
        throw new Exception(
            'When setting a value in the cache, you must pass an array of key / value pairs.'
        );
    }

    return app('cache')->put(key($arguments[0]), reset($arguments[0]), $arguments[1] ?? null);
}

/**
 * Get / set the specified configuration value.
 *
 * If an array is passed as the key, we will assume you want to set an array of values.
 *
 * @param  array|string|null  $key
 * @param  mixed  $default
 * @return mixed|\Illuminate\Config\Repository
 *
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/7.x/src/Illuminate/Foundation/helpers.php
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
 * Get the configuration path.
 *
 * @param  string  $path
 * @return string
 *
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/7.x/src/Illuminate/Foundation/helpers.php
 */
function config_path($path = '')
{
    return app()->configPath($path);
}

/**
 * Get the database path.
 *
 * @param  string  $path
 * @return string
 *
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/7.x/src/Illuminate/Foundation/helpers.php
 */
function database_path($path = '')
{
    return app()->databasePath($path);
}

/**
 * Decrypt the given value.
 *
 * @param  string  $value
 * @param  bool  $unserialize
 * @return mixed
 *
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/7.x/src/Illuminate/Foundation/helpers.php
 */
function decrypt($value, $unserialize = true)
{
    return app('encrypter')->decrypt($value, $unserialize);
}

/**
 * Encrypt the given value.
 *
 * @param  mixed  $value
 * @param  bool  $serialize
 * @return string
 *
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/7.x/src/Illuminate/Foundation/helpers.php
 */
function encrypt($value, $serialize = true)
{
    return app('encrypter')->encrypt($value, $serialize);
}

/**
 * Write some information to the log.
 *
 * @param  string  $message
 * @param  array  $context
 * @return void
 *
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/7.x/src/Illuminate/Foundation/helpers.php
 */
function info($message, $context = [])
{
    app('log')->info($message, $context);
}

/**
 * Log a debug message to the logs.
 *
 * @param  string|null  $message
 * @param  array  $context
 * @return \Illuminate\Log\LogManager|null
 *
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/7.x/src/Illuminate/Foundation/helpers.php
 */
function logger($message = null, array $context = [])
{
    if (is_null($message)) {
        return app('log');
    }

    return app('log')->debug($message, $context);
}

/**
 * Get a log driver instance.
 *
 * @param  string|null  $driver
 * @return \Illuminate\Log\LogManager|\Psr\Log\LoggerInterface
 *
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/7.x/src/Illuminate/Foundation/helpers.php
 */
function logs($driver = null)
{
    return $driver ? app('log')->driver($driver) : app('log');
}

/**
 * Create a new Carbon instance for the current time.
 *
 * @param  \DateTimeZone|string|null  $tz
 * @return \Illuminate\Support\Carbon
 *
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/7.x/src/Illuminate/Foundation/helpers.php
 */
function now($tz = null)
{
    return Date::now($tz);
}

/**
 * Get the path to the public folder.
 *
 * @param  string  $path
 * @return string
 *
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/7.x/src/Illuminate/Foundation/helpers.php
 */
function public_path($path = '')
{
    return app()->make('path.public') . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $path);
}

/**
 * Report an exception.
 *
 * @param  \Throwable  $exception
 * @return void
 *
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/7.x/src/Illuminate/Foundation/helpers.php
 */
function report(Throwable $exception)
{
    app(ExceptionHandler::class)->report($exception);
}

/**
 * Catch a potential exception and return a default value.
 *
 * @param  callable  $callback
 * @param  mixed  $rescue
 * @param  bool  $report
 * @return mixed
 *
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/7.x/src/Illuminate/Foundation/helpers.php
 */
function rescue(callable $callback, $rescue = null, $report = true)
{
    try {
        return $callback();
    } catch (Throwable $e) {
        if ($report) {
            report($e);
        }

        return $rescue instanceof Closure ? $rescue($e) : $rescue;
    }
}

/**
 * Resolve a service from the container.
 *
 * @param  string  $name
 * @param  array  $parameters
 * @return mixed
 *
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/7.x/src/Illuminate/Foundation/helpers.php
 */
function resolve($name, array $parameters = [])
{
    return app($name, $parameters);
}

/**
 * Get the path to the resources folder.
 *
 * @param  string  $path
 * @return string
 *
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/7.x/src/Illuminate/Foundation/helpers.php
 */
function resource_path($path = '')
{
    return app()->resourcePath($path);
}

/**
 * Get the path to the storage folder.
 *
 * @param  string  $path
 * @return string
 *
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/7.x/src/Illuminate/Foundation/helpers.php
 */
function storage_path($path = '')
{
    return app()->storagePath() . ($path ? DIRECTORY_SEPARATOR . $path : $path);
}

/**
 * Create a new Carbon instance for the current date.
 *
 * @param  \DateTimeZone|string|null  $tz
 * @return \Illuminate\Support\Carbon
 *
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/7.x/src/Illuminate/Foundation/helpers.php
 */
function today($tz = null)
{
    return Date::today($tz);
}

/**
 * Get the evaluated view contents for the given view.
 *
 * @param  string|null  $view
 * @param  \Illuminate\Contracts\Support\Arrayable|array  $data
 * @param  array  $mergeData
 * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
 *
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/7.x/src/Illuminate/Foundation/helpers.php
 */
function view($view = null, $data = [], $mergeData = [])
{
    $factory = app(ViewFactory::class);

    if (func_num_args() === 0) {
        return $factory;
    }

    return $factory->make($view, $data, $mergeData);
}

/**
 * Initialize the Acorn bootloader.
 *
 * @param  callable|null $callback
 * @return void
 */
function bootloader($callback = null)
{
    static $bootloader;

    if (! $bootloader) {
        $bootloader = new \Roots\Acorn\Bootloader();
    }

    if (is_callable($callback)) {
        $bootloader->call($callback);
    }
}
