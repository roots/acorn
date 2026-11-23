<?php

namespace Roots\Acorn\Pagination;

use Illuminate\Pagination\CursorPaginator as CursorPaginatorBase;
use Illuminate\Pagination\LengthAwarePaginator as LengthAwarePaginatorBase;
use Illuminate\Pagination\PaginationServiceProvider as PaginationServiceProviderBase;
use Illuminate\Pagination\Paginator as PaginatorBase;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;
use ReflectionClass;

class PaginationServiceProvider extends PaginationServiceProviderBase
{
    /**
     * The pagination views to compile with Laravel's translator, keyed by the class next to them.
     *
     * @var array<class-string, string>
     */
    protected array $paginationViews = [
        PaginationServiceProviderBase::class => 'resources/views',
        'Livewire\Features\SupportPagination\SupportPagination' => 'views',
    ];

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
     * Compile `__()` in bundled pagination views to `trans()`.
     *
     * WordPress defines its own `__()` before Laravel's helpers load,
     * so the bundled views would call WordPress's version instead.
     */
    protected function useLaravelTranslator(BladeCompiler $blade): void
    {
        $paths = $this->paginationViewPaths();

        $blade->precompiler(function (string $value) use ($blade, $paths) {
            // The compiler keeps the last compiled path, so strings compiled after
            // a pagination view would match it too without the `$paginator` check.
            if (! Str::startsWith((string) $blade->getPath(), $paths) || ! str_contains($value, '$paginator')) {
                return $value;
            }

            return preg_replace('/(?<![\w$>:\\\\])__\(/', 'trans(', $value);
        });
    }

    /**
     * Get the paths to the bundled pagination views that are installed.
     *
     * @return list<string>
     */
    protected function paginationViewPaths(): array
    {
        return collect($this->paginationViews)
            ->filter(fn ($path, $class) => class_exists($class))
            ->map(fn ($path, $class) => dirname((new ReflectionClass($class))->getFileName()) . "/{$path}/")
            ->values()
            ->all();
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
