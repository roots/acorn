<?php

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator as CursorPaginatorBase;
use Illuminate\Pagination\LengthAwarePaginator as LengthAwarePaginatorBase;
use Illuminate\Pagination\PaginationServiceProvider as PaginationServiceProviderBase;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Route;
use Roots\Acorn\Application;
use Roots\Acorn\Pagination\CursorPaginator;
use Roots\Acorn\Pagination\LengthAwarePaginator;
use Roots\Acorn\Pagination\PaginationServiceProvider;
use Roots\Acorn\Pagination\Paginator as AcornPaginator;
use Roots\Acorn\ServiceProvider;
use Roots\Acorn\Tests\Test\TestCase;
use Roots\Acorn\View\ViewServiceProvider;
use Symfony\Component\Process\Process;

use function Roots\Acorn\Tests\temp;

uses(TestCase::class);

function renderLinks(string $view, string $url, ?string $provider = null): Process
{
    $process = new Process([
        PHP_BINARY,
        '-d',
        'display_errors=stderr',
        __DIR__ . '/__fixtures__/render_links.php',
        temp('pagination/' . uniqid()),
        $view,
        $url,
        ...array_filter([$provider]),
    ]);

    $process->run();

    return $process;
}

function paginationViews(): array
{
    $path = dirname((new ReflectionClass(PaginationServiceProviderBase::class))->getFileName()) . '/resources/views';

    return array_map(fn ($file) => basename($file, '.blade.php'), glob("{$path}/*.blade.php"));
}

beforeEach(function () {
    $this->app = new Application(temp('pagination'));
    $this->app->instance('config', new Repository([
        'view' => [
            'paths' => [__DIR__ . '/__fixtures__/views'],
            'compiled' => temp('pagination/' . uniqid()) . '/views',
            'namespaces' => [],
            'directives' => [],
            'components' => [],
            'composers' => [],
        ],
    ]));
    $this->app->instance('events', new Dispatcher($this->app));
    $this->app->instance('files', new Filesystem());
    $this->app->instance('request', Request::create('http://example.test/category/news/'));
    $this->app->register(ViewServiceProvider::class);
    $this->app->register(PaginationServiceProvider::class);
    $this->app->boot();

    $this->compile = function (string $view): string {
        $path = $this->app['view']->getFinder()->find($view);
        $blade = $this->app['blade.compiler'];

        $blade->compile($path);

        return file_get_contents($blade->getCompiledPath($path));
    };

    $this->stub('is_admin', fn () => false);
    $this->stub('user_trailingslashit', function ($path) {
        $path = rtrim($path, '/\\');

        return $GLOBALS['wp_rewrite']->use_trailing_slashes ? "{$path}/" : $path;
    });

    $this->usePermalinks = function (bool $trailingSlashes = true): void {
        $GLOBALS['wp_rewrite'] = (object) ['use_trailing_slashes' => $trailingSlashes];
    };
});

afterEach(function () {
    unset($GLOBALS['wp_query'], $GLOBALS['wp_rewrite']);
});

/**
 * Make a paginator the way Eloquent and the query builder do.
 */
function makePaginator(string $type): LengthAwarePaginatorBase|Paginator|CursorPaginatorBase
{
    $options = ['path' => Paginator::resolveCurrentPath()];

    return match ($type) {
        'length aware' => Container::getInstance()->makeWith(LengthAwarePaginatorBase::class, [
            'items' => [1, 2, 3],
            'total' => 9,
            'perPage' => 3,
            'currentPage' => 1,
            'options' => $options,
        ]),
        'simple' => Container::getInstance()->makeWith(Paginator::class, [
            'items' => [1, 2, 3, 4],
            'perPage' => 3,
            'currentPage' => 1,
            'options' => $options,
        ]),
        'cursor' => Container::getInstance()->makeWith(CursorPaginatorBase::class, [
            'items' => [1, 2, 3],
            'perPage' => 3,
            'cursor' => null,
            'options' => $options,
        ]),
    };
}

function paginatorUrl(LengthAwarePaginatorBase|Paginator|CursorPaginatorBase $paginator): string
{
    return $paginator instanceof CursorPaginatorBase ? $paginator->url(new Cursor(['id' => 3])) : $paginator->url(2);
}

it("replaces Laravel's pagination provider", function () {
    expect(ServiceProvider::defaultProviders()->toArray())
        ->toContain(PaginationServiceProvider::class)
        ->not->toContain(PaginationServiceProviderBase::class);
});

it("fails to render Laravel's pagination views with WordPress's __()", function () {
    $process = renderLinks('tailwind', 'http://example.test/category/news/', PaginationServiceProviderBase::class);

    expect($process->isSuccessful())
        ->toBeFalse()
        ->and($process->getErrorOutput())
        ->toContain('Cannot access offset of type array in isset or empty');
});

it("renders Laravel's pagination views with WordPress's __()", function (string $view) {
    $process = renderLinks($view, 'http://example.test/category/news/?page=2');

    expect($process->getErrorOutput())
        ->toBe('')
        ->and($process->getOutput())
        ->toContain('Next')
        ->toContain('href="http://example.test/category/news?page=3"')
        ->not->toContain('pagination.next')
        ->not->toContain('pagination.previous');
})->with(paginationViews());

it('compiles __() to trans() in pagination views', function () {
    expect(($this->compile)('pagination::tailwind'))
        ->toContain("trans('pagination.previous')")
        ->toContain("trans('Go to page :page', ['page' => \$page])")
        ->not->toContain("__('");
});

it('leaves __() alone in other views', function () {
    expect(($this->compile)('greeting'))->toContain("__('Hello', 'sage')");
});

it('leaves __() alone in strings compiled after a pagination view', function () {
    ($this->compile)('pagination::tailwind');

    expect($this->app['blade.compiler']->compileString("{{ __('Hello', 'sage') }}"))->toContain("__('Hello', 'sage')");
});

it('resolves the current page from the request', function () {
    $this->app->instance('request', Request::create('http://example.test/category/news/?page=3'));

    expect(Paginator::resolveCurrentPage())->toBe(3);
});

it('resolves the current page from the WordPress query', function () {
    $this->stub('get_query_var', fn ($name) => ['paged' => '2'][$name] ?? '');
    $GLOBALS['wp_query'] = new stdClass();

    $this->app->instance('request', Request::create('http://example.test/category/news/page/2/'));

    expect(Paginator::resolveCurrentPage('paged'))->toBe(2);
});

it('only resolves the current page from the paged query var', function () {
    $this->stub('get_query_var', fn () => '2');
    $GLOBALS['wp_query'] = new stdClass();

    $this->app->instance('request', Request::create('http://example.test/hello-world/2/'));

    expect(Paginator::resolveCurrentPage())->toBe(1)->and(Paginator::resolveCurrentPage('cpage'))->toBe(1);
});

it('prefers the request over the WordPress query', function () {
    $this->stub('get_query_var', fn () => '2');
    $GLOBALS['wp_query'] = new stdClass();

    $this->app->instance('request', Request::create('http://example.test/category/news/page/2/?paged=4'));

    expect(Paginator::resolveCurrentPage('paged'))->toBe(4);
});

it('ignores invalid page numbers', function (string $page) {
    $this->app->instance('request', Request::create("http://example.test/category/news/?page={$page}"));

    expect(Paginator::resolveCurrentPage())->toBe(1);
})->with(['0', '-1', 'abc', '1.5']);

it('resolves the current path from the request', function () {
    expect(Paginator::resolveCurrentPath())->toBe('http://example.test/category/news');
});

it('resolves Acorn paginators from the container', function (string $type, string $class) {
    expect(makePaginator($type))->toBeInstanceOf($class);
})->with([
    ['length aware', LengthAwarePaginator::class],
    ['simple',       AcornPaginator::class],
    ['cursor',       CursorPaginator::class],
]);

it('adds a trailing slash to page links on WordPress requests', function (string $type) {
    ($this->usePermalinks)();

    expect(paginatorUrl(makePaginator($type)))->toStartWith('http://example.test/category/news/?');
})->with(['length aware', 'simple', 'cursor']);

it('adds a trailing slash to page links on the WordPress route', function () {
    ($this->usePermalinks)();

    $request = Request::create('http://example.test/category/news/');
    $request->setRouteResolver(fn () => (new Route('GET', '{any?}', fn () => null))->name('wordpress'));
    $this->app->instance('request', $request);

    expect(makePaginator('length aware')->url(2))->toBe('http://example.test/category/news/?page=2');
});

it('leaves page links alone on Laravel routes', function () {
    ($this->usePermalinks)();

    $request = Request::create('http://example.test/welcome/');
    $request->setRouteResolver(fn () => (new Route('GET', 'welcome', fn () => null))->name('welcome'));
    $this->app->instance('request', $request);

    expect(makePaginator('length aware')->url(2))->toBe('http://example.test/welcome?page=2');
});

it('leaves page links alone when the path has a query string', function () {
    ($this->usePermalinks)();

    expect(makePaginator('length aware')->withPath('http://example.test/?p=1')->url(2))
        ->toBe('http://example.test/?p=1&page=2');
});

it('leaves page links alone outside of WordPress', function () {
    expect(makePaginator('length aware')->url(2))->toBe('http://example.test/category/news?page=2');
});

it('leaves page links alone for files', function (string $url) {
    ($this->usePermalinks)();

    $this->app->instance('request', Request::create($url));

    expect(makePaginator('length aware')->url(2))->toBe(strtok($url, '?') . '?page=2');
})->with([
    'http://example.test/wp-admin/admin-ajax.php?action=list',
    'http://example.test/wp-login.php',
    'http://example.test/sitemap.xml',
]);

it('leaves page links alone on admin requests', function () {
    ($this->usePermalinks)();
    $this->stub('is_admin', fn () => true);

    $this->app->instance('request', Request::create('http://example.test/wp-admin/'));

    expect(makePaginator('length aware')->url(2))->toBe('http://example.test/wp-admin?page=2');
});

it("doesn't remove slashes when permalinks don't end with one", function () {
    ($this->usePermalinks)(false);

    expect(makePaginator('length aware')->withPath('/')->url(2))
        ->toBe('/?page=2')
        ->and(makePaginator('length aware')->withPath('/blog/')->url(2))
        ->toBe('/blog/?page=2')
        ->and(makePaginator('length aware')->url(2))
        ->toBe('http://example.test/category/news?page=2');
});
