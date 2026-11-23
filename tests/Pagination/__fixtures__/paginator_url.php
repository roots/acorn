<?php

/**
 * Print a paginator's page 2 URL on a WordPress request, optionally a REST request.
 *
 * Runs in a separate process since `REST_REQUEST` can't be undefined between tests.
 *
 * Usage: php paginator_url.php <base-path> <url> [rest]
 */

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Roots\Acorn\Application;
use Roots\Acorn\Pagination\PaginationServiceProvider;

function user_trailingslashit($url, $type_of_url = '')
{
    return rtrim($url, '/\\') . '/';
}

function is_admin()
{
    return false;
}

require __DIR__ . '/../../../vendor/autoload.php';

[, $base, $url] = $argv;

if (($argv[3] ?? null) === 'rest') {
    define('REST_REQUEST', true);
}

$GLOBALS['wp_rewrite'] = (object) ['use_trailing_slashes' => true];

$app = new Application($base);
$app->instance('request', Request::create($url));
$app->register(PaginationServiceProvider::class);
$app->boot();

echo Container::getInstance()->makeWith(LengthAwarePaginator::class, [
    'items' => [1, 2, 3],
    'total' => 9,
    'perPage' => 3,
    'currentPage' => 1,
    'options' => ['path' => Paginator::resolveCurrentPath()],
])->url(2);
