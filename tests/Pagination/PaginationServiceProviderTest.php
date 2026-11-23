<?php

use Illuminate\Config\Repository;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Pagination\PaginationServiceProvider as PaginationServiceProviderBase;
use Illuminate\Pagination\Paginator;
use Roots\Acorn\Application;
use Roots\Acorn\Pagination\PaginationServiceProvider;
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
});

afterEach(function () {
    unset($GLOBALS['wp_query']);
});

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
        ->not->toMatch('/(?<![\w$>:\\\\])__\(/');
});

it('leaves __() alone in other views', function () {
    expect(($this->compile)('greeting'))->toContain("__('Hello', 'sage')");
});

it('resolves the current page from the request', function () {
    $this->app->instance('request', Request::create('http://example.test/category/news/?page=3'));

    expect(Paginator::resolveCurrentPage())->toBe(3);
});

it('resolves the current page from the WordPress query', function () {
    $this->stub('get_query_var', fn ($name) => ['paged' => '2'][$name] ?? '');
    $GLOBALS['wp_query'] = new stdClass();

    $this->app->instance('request', Request::create('http://example.test/category/news/page/2/'));

    expect(Paginator::resolveCurrentPage('paged'))->toBe(2)->and(Paginator::resolveCurrentPage())->toBe(1);
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
