<?php

namespace Roots\Acorn\Sage;

use Illuminate\Support\ServiceProvider;
use Roots\Acorn\Sage\Sage;
use Roots\Acorn\Sage\ViewFinder;

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

        add_filters([
            'index_template_hierarchy',
            '404_template_hierarchy',
            'archive_template_hierarchy',
            'author_template_hierarchy',
            'category_template_hierarchy',
            'tag_template_hierarchy',
            'taxonomy_template_hierarchy',
            'date_template_hierarchy',
            'home_template_hierarchy',
            'frontpage_template_hierarchy',
            'page_template_hierarchy',
            'paged_template_hierarchy',
            'search_template_hierarchy',
            'single_template_hierarchy',
            'singular_template_hierarchy',
            'attachment_template_hierarchy',
            'privacypolicy_template_hierarchy',
            'embed_template_hierarchy',
        ], $sage->filter('template_hierarchy'), 10);
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
