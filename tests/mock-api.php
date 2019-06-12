<?php

define('WP_CONTENT_DIR', 'vfs://__fixtures__/app');

define('ABSPATH', 'vfs://__fixtures__/wp');

define('STYLESHEETPATH', WP_CONTENT_DIR . '/themes/sage-child');
define('TEMPLATEPATH', WP_CONTENT_DIR . '/themes/sage');

$GLOBALS['mock-hooks'] = [];

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

if (! function_exists('locate_template')) {
    function locate_template($template_names = [])
    {
        $template_names = array_filter((array) $template_names);

        foreach ($template_names as $template_name) {
            if (file_exists($template = STYLESHEETPATH . '/' . $template_name)) {
                return $template;
            }

            if (file_exists($template = TEMPLATEPATH . '/' . $template_name)) {
                return $template;
            }
        }

        return '';
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
    function add_filter($key, callable $callback)
    {
        $GLOBALS['mock-hooks'][$key] = $callback;

        return null;
    }
}

if (! function_exists('apply_filters')) {
    function apply_filters($key, $value = null)
    {
        if (array_key_exists($key, $GLOBALS['mock-hooks'])) {
            $callback = $GLOBALS['mock-hooks'][$key];
            unset($GLOBALS['mock-hooks'][$key]);
            $value = call_user_func($callback, $value);
        }

        return $value;
    }
}

if (! function_exists('doing_filter')) {
    function doing_filter($key)
    {
        return ! array_key_exists($key, $GLOBALS['mock-hooks']);
    }
}

if (! function_exists('add_action')) {
    function add_action($key, $callback)
    {
        return add_filter($key, $callback);
    }
}

if (! function_exists('do_action')) {
    function do_action($key, $value = null)
    {
        return apply_filters($key, $value);
    }
}

if (! function_exists('did_action')) {
    function did_action($key)
    {
        return doing_filter($key);
    }
}

if (! function_exists('doing_action')) {
    function doing_action($key)
    {
        return doing_filter($key);
    }
}
