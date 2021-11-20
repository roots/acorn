<?php

if (! function_exists('asset')) {
    function asset(string $asset)
    {
        Roots\asset($asset);
    }
}

if (! function_exists('view')) {
    function view(string $asset)
    {
        Roots\view($asset);
    }
}
