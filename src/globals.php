<?php

/**
 * Application Functions
 */
if (! function_exists('app')) {
    function app(...$args)
    {
        return Roots\app(...$args);
    }
}

if (! function_exists('config')) {
    function config(...$args)
    {
        return Roots\config(...$args);
    }
}

if (! function_exists('asset')) {
    function asset(...$args)
    {
        return Roots\asset(...$args);
    }
}

if (! function_exists('view')) {
    function view(...$args)
    {
        return Roots\view(...$args);
    }
}


/**
 * Helper functions
 */
if (! function_exists('env')) {
    function env(...$args)
    {
        return Roots\env(...$args);
    }
}

if (! function_exists('add_actions')) {
    function add_actions(...$args)
    {
        return Roots\add_actions(...$args);
    }
}

if (! function_exists('add_filters')) {
    function add_filters(...$args)
    {
        return Roots\add_filters(...$args);
    }
}

if (! function_exists('remove_actions')) {
    function remove_actions(...$args)
    {
        return Roots\remove_actions(...$args);
    }
}

if (! function_exists('remove_filters')) {
    function remove_filters(...$args)
    {
        return Roots\remove_filters(...$args);
    }
}
