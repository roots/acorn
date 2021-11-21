<?php

if (! function_exists('asset')) {
    function asset(string $asset)
    {
        return Roots\asset($asset);
    }
}

if (! function_exists('view')) {
    function view()
    {
        return Roots\view(...func_get_args());
    }
}
