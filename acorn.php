<?php

/**
 * Plugin Name:   Acorn
 * Plugin URI:    https://roots.io/acorn
 * Description:   Lazy-loaded framework for WordPress themes and plugins
 * Version:       1.0.0
 * Author:        Roots
 * Author URI:    https://roots.io
 * License:       MIT
 * License URI:   http://opensource.org/licenses/MIT
 */

require_once __DIR__ . '/vendor/autoload.php';

add_action('after_setup_theme', 'Roots\bootloader', 5);
