<?php

/**
 * Render a paginator's links with WordPress's `__()` defined first.
 *
 * Runs in a separate process since Laravel's `__()` is already defined in the test suite.
 *
 * Usage: php render_links.php <base-path> <view> <url> [<provider>]
 *
 * The view is a pagination view name, `livewire::<name>` to render a stand-in for
 * Livewire's pagination views, or `json` to print the paginator's links as JSON.
 */

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
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

if (str_starts_with($view, 'livewire::')) {
    require __DIR__ . '/livewire/SupportPagination.php';
}

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

$app['view']->addNamespace('livewire', __DIR__ . '/livewire/views');

$paginator = Container::getInstance()->makeWith(LengthAwarePaginator::class, [
    'items' => range(1, 3),
    'total' => 9,
    'perPage' => 3,
    'currentPage' => Paginator::resolveCurrentPage(),
    'options' => ['path' => Paginator::resolveCurrentPath()],
]);

if ($view === 'json') {
    echo json_encode($paginator->toArray()['links']);

    exit();
}

echo $paginator->links(str_contains($view, '::') ? $view : "pagination::{$view}");
