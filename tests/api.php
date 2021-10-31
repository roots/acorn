<?php

use function Roots\Acorn\Tests\temp;

if (! defined('ABSPATH')) {
    define('ABSPATH', temp('wp'));
}

if (! defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', temp('app'));
}
