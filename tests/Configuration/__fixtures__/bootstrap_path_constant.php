<?php

/**
 * Resolve the default bootstrap path with an `ACORN_BOOTSTRAP_PATH` constant.
 *
 * Runs in a separate process since constants can't be undefined between tests.
 *
 * Usage: php bootstrap_path_constant.php <base-path> <constant-value> [<storage-path>]
 *
 * With a storage path, it's passed to `withPaths()` and the application's bootstrap path is printed.
 */

use Roots\Acorn\Application;
use Roots\Acorn\Configuration\ApplicationBuilder;

require __DIR__ . '/../../../vendor/autoload.php';

function get_theme_file_path($path = '')
{
    return '/nonexistent-theme/' . $path;
}

define('WP_CONTENT_DIR', $argv[1] . '/content');
define('ACORN_BOOTSTRAP_PATH', $argv[2]);

if (isset($argv[3])) {
    $app = new Application($argv[1]);

    (new ApplicationBuilder($app))->withPaths(storage: $argv[3]);

    echo $app->bootstrapPath();

    exit();
}

echo (new ApplicationBuilder(new Application($argv[1])))->defaultPaths()['bootstrap'];
