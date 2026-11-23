<?php

/**
 * Render a paginator's links with WordPress's `__()` defined first.
 *
 * Runs in a separate process since Laravel's `__()` is already defined in the test suite.
 *
 * Usage: php render_links.php <base-path> <view> <url> [<provider>]
 */

use Illuminate\Config\Repository;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Translation\TranslationServiceProvider;
use Roots\Acorn\Application;
use Roots\Acorn\Pagination\PaginationServiceProvider;
use Roots\Acorn\View\ViewServiceProvider;

/**
 * Mirrors WordPress's `__()`, which looks the text domain up as an array key.
 */
function __($text, $domain = 'default')
{
    static $l10n = [];

    return isset($l10n[$domain]) ? $l10n[$domain] : $text;
}

require __DIR__ . '/../../../vendor/autoload.php';

[, $base, $view, $url] = $argv;

$provider = $argv[4] ?? PaginationServiceProvider::class;

$app = new Application($base);

$app->instance('config', new Repository([
    'app' => ['locale' => 'en', 'fallback_locale' => 'en'],
    'view' => [
        'paths' => [],
        'compiled' => "{$base}/views",
        'namespaces' => [],
        'directives' => [],
        'components' => [],
        'composers' => [],
    ],
]));
$app->instance('events', new Dispatcher($app));
$app->instance('files', new Filesystem());
$app->instance('request', Request::create($url));

$app->register(TranslationServiceProvider::class);
$app->register(ViewServiceProvider::class);
$app->register($provider);
$app->boot();

$paginator = new LengthAwarePaginator(range(1, 3), 9, 3, Paginator::resolveCurrentPage(), [
    'path' => Paginator::resolveCurrentPath(),
]);

echo $paginator->links("pagination::{$view}");
