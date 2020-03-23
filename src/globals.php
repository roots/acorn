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

if (! function_exists('app_path')) {
    function app_path(...$args)
    {
        return Roots\app_path(...$args);
    }
}

if (! function_exists('asset')) {
    function asset(...$args)
    {
        return Roots\asset(...$args);
    }
}

if (! function_exists('base_path')) {
    function base_path(...$args)
    {
        return Roots\base_path(...$args);
    }
}

if (! function_exists('bcrypt')) {
    function bcrypt(...$args)
    {
        return Roots\bcrypt(...$args);
    }
}

if (! function_exists('broadcast')) {
    function broadcast(...$args)
    {
        return Roots\broadcast(...$args);
    }
}

if (! function_exists('cache')) {
    function cache(...$args)
    {
        return Roots\cache(...$args);
    }
}

if (! function_exists('config')) {
    function config(...$args)
    {
        return Roots\config(...$args);
    }
}

if (! function_exists('config_path')) {
    function config_path(...$args)
    {
        return Roots\config_path(...$args);
    }
}

if (! function_exists('database_path')) {
    function database_path(...$args)
    {
        return Roots\database_path(...$args);
    }
}

if (! function_exists('decrypt')) {
    function decrypt(...$args)
    {
        return Roots\decrypt(...$args);
    }
}

if (! function_exists('encrypt')) {
    function encrypt(...$args)
    {
        return Roots\encrypt(...$args);
    }
}

if (! function_exists('info')) {
    function info(...$args)
    {
        return Roots\info(...$args);
    }
}

if (! function_exists('logger')) {
    function logger(...$args)
    {
        return Roots\logger(...$args);
    }
}

if (! function_exists('logs')) {
    function logs(...$args)
    {
        return Roots\logs(...$args);
    }
}

if (! function_exists('now')) {
    function now(...$args)
    {
        return Roots\now(...$args);
    }
}

if (! function_exists('public_path')) {
    function public_path(...$args)
    {
        return Roots\public_path(...$args);
    }
}

if (! function_exists('report')) {
    function report(...$args)
    {
        return Roots\report(...$args);
    }
}

if (! function_exists('rescue')) {
    function rescue(...$args)
    {
        return Roots\rescue(...$args);
    }
}

if (! function_exists('resolve')) {
    function resolve(...$args)
    {
        return Roots\resolve(...$args);
    }
}

if (! function_exists('resource_path')) {
    function resource_path(...$args)
    {
        return Roots\resource_path(...$args);
    }
}

if (! function_exists('storage_path')) {
    function storage_path(...$args)
    {
        return Roots\storage_path(...$args);
    }
}

if (! function_exists('today')) {
    function today(...$args)
    {
        return Roots\today(...$args);
    }
}

if (! function_exists('view')) {
    function view(...$args)
    {
        return Roots\view(...$args);
    }
}

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
