<?php

/**
 * Plugin Name:   Acorn
 * Plugin URI:    https://roots.io/acorn
 * Description:   Lazy-loaded framework for WordPress themes and plugins
 * Version:       3.x-dev
 * Author:        Roots
 * Author URI:    https://roots.io
 * License:       MIT
 * License URI:   http://opensource.org/licenses/MIT
 */

require_once __DIR__ . '/vendor/autoload.php';

Roots\add_actions(['plugins_loaded', 'rest_api_init'], \Roots\bootloader(), 5);
