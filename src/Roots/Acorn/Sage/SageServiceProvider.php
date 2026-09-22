<?php

namespace Roots\Acorn\Sage;

use Illuminate\Support\ServiceProvider;

use function Roots\add_filters;

class SageServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('sage', Sage::class);
        $this->app->bind('sage.finder', ViewFinder::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->bindCompatFilters();
        $this->bindViewFilters();
    }

    /**
     * Sage compatibility filters
     *
     * These are filters that are required for Sage features to operate correctly
     *
     * @return void
     */
    protected function bindCompatFilters()
    {
        $sage = $this->app['sage'];

        add_filter('body_class', $sage->filter('body_class'), 10);
        add_action('the_post', $sage->filter('the_post'), 10);
        add_filter('template_include', $sage->filter('template_include'), 100);
        add_filter('theme_templates', $sage->filter('theme_templates'), 100, 4);
        add_filter('script_loader_tag', $sage->filter('script_loader_tag'), 100, 3);

        $types = [
            'index',
            '404',
            'archive',
            'author',
            'category',
            'tag',
            'taxonomy',
            'date',
            'home',
            'frontpage',
            'page',
            'paged',
            'search',
            'single',
            'singular',
            'attachment',
            'privacypolicy',
            'embed',
        ];

        add_filters(
            array_map(fn ($type) => "{$type}_template_hierarchy", $types),
            $sage->filter('template_hierarchy'),
            10,
        );

        add_filters(array_map(fn ($type) => "{$type}_template", $types), $sage->filter('template'), 10, 3);
    }

    /**
     * Sage view filters
     *
     * These filters direct WordPress to views within Sage.
     *
     * @return void
     */
    protected function bindViewFilters()
    {
        $sage = $this->app['sage'];

        add_filter('comments_template', $sage->filter('comments_template'), 10);
        add_filter('get_search_form', $sage->filter('search_form'), 10);
    }
}
