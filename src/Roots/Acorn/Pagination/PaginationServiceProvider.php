<?php

namespace Roots\Acorn\Pagination;

use Illuminate\Pagination\CursorPaginator as CursorPaginatorBase;
use Illuminate\Pagination\LengthAwarePaginator as LengthAwarePaginatorBase;
use Illuminate\Pagination\PaginationServiceProvider as PaginationServiceProviderBase;
use Illuminate\Pagination\Paginator as PaginatorBase;
use Illuminate\View\Compilers\BladeCompiler;
use ReflectionClass;

class PaginationServiceProvider extends PaginationServiceProviderBase
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->app->bind(LengthAwarePaginatorBase::class, LengthAwarePaginator::class);
        $this->app->bind(PaginatorBase::class, Paginator::class);
        $this->app->bind(CursorPaginatorBase::class, CursorPaginator::class);

        PaginatorBase::currentPageResolver(function ($pageName = 'page') {
            $page = $this->app['request']->input($pageName);

            if (blank($page) && $pageName === 'paged') {
                $page = $this->pagedQueryVar();
            }

            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return (int) $page;
            }

            return 1;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->callAfterResolving('blade.compiler', fn (BladeCompiler $blade) => $this->useLaravelTranslator($blade));
    }

    /**
     * Compile `__()` in Laravel's pagination views to `trans()`.
     *
     * WordPress defines its own `__()` before Laravel's helpers load,
     * so the bundled views would call WordPress's version instead.
     */
    protected function useLaravelTranslator(BladeCompiler $blade): void
    {
        $views = $this->viewsPath();

        $blade->precompiler(function (string $value) use ($blade, $views) {
            if (! str_starts_with((string) $blade->getPath(), $views) || ! str_contains($value, '$paginator')) {
                return $value;
            }

            return preg_replace('/(?<![\w$>:\\\\])__\(/', 'trans(', $value);
        });
    }

    /**
     * Get the path to Laravel's bundled pagination views.
     */
    protected function viewsPath(): string
    {
        $file = (new ReflectionClass(PaginationServiceProviderBase::class))->getFileName();

        return dirname($file) . '/resources/views/';
    }

    /**
     * Get WordPress's `paged` query var.
     *
     * With pretty permalinks, WordPress moves `?paged=2` into the path
     * (`/page/2/`), so it's only available from the parsed query.
     */
    protected function pagedQueryVar(): mixed
    {
        if (! function_exists('get_query_var') || ! isset($GLOBALS['wp_query'])) {
            return null;
        }

        return get_query_var('paged');
    }
}
