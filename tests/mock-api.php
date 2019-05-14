<?php

define('WP_CONTENT_DIR', 'vfs://__fixtures__/app');

define('ABSPATH', 'vfs://__fixtures__/wp');

if (! function_exists('content_url')) {
    function content_url()
    {
        return '/app';
    }
}

if (! function_exists('site_url')) {
    function site_url()
    {
        return '/wp';
    }
}

if (! function_exists('get_parent_theme_file_path')) {
    function get_parent_theme_file_path($file = '')
    {
        return get_theme_file_path($file);
    }
}

if (! function_exists('get_theme_file_path')) {
    function get_theme_file_path($file = '')
    {
        $file = ltrim($file, '/\\');
        return WP_CONTENT_DIR . "/themes/sage/{$file}";
    }
}

if (! function_exists('get_theme_file_uri')) {
    function get_theme_file_uri($file = '')
    {
        $file = ltrim($file, '/\\');
        return "/app/themes/sage/{$file}";
    }
}

if (! function_exists('wp_upload_dir')) {
    function wp_upload_dir()
    {
        return [
            'path' => WP_CONTENT_DIR . '/uploads/test',
            'url' => content_url() . '/uploads/test',
            'subdir' => '/test',
            'basedir' => WP_CONTENT_DIR . '/uploads',
            'baseurl' => content_url() . '/uploads'
        ];
    }
}

if (! function_exists('add_filter')) {
    function add_filter()
    {
        return null;
    }
}

if (! function_exists('add_action')) {
    function add_action()
    {
        return null;
    }
}

if (! function_exists('did_action')) {
    function did_action()
    {
        return null;
    }
}
