<?php

namespace Roots\Sage;

use function Roots\view;
use function Roots\add_filters;
use Illuminate\Container\Container;

class Sage
{
    /** @var \Roots\Acorn\Application Application container */
    protected $app;

    public function __construct(Container $app)
    {
        $this->setContainer($app);
    }

    public function attach()
    {
        $this->attachPostVariable();
        $this->attachCommentsTemplateResolver();
        $this->attachTemplateHierarchyFilters();
        $this->attachTemplateIncludeFilter();
    }

    public function attachPostVariable($priority = 10)
    {
        add_action('the_post', function ($post) {
            $this->app['view']->share('post', $post);
        }, $priority);
    }

    public function attachCommentsTemplateResolver($priority = 100)
    {
        add_filter('comments_template', [$this, 'commentsTemplateResolver'], $priority);
    }

    public function attachTemplateHierarchyFilters($priority = 10)
    {
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
        ], [$this, 'filterTemplates'], $priority);
    }

    public function attachTemplateIncludeFilter($priority = 100)
    {
        add_filter('template_include', [$this, 'loadView'], $priority);
    }

    public function commentsTemplateResolver($file)
    {
        $file = str_replace([STYLESHEETPATH, TEMPLATEPATH], '', $file);
        $view = $this->app['view.finder']->getPossibleViewNameFromPath($file);
        return view($view)->makeLoader();
    }

    public function getViewFromGivenThemePath($path)
    {
        $relativePath = str_replace([STYLESHEETPATH, TEMPLATEPATH], '', $path);
    }

    /**
     * Template Hierarchy should search for .blade.php files
     *
     * @internal Used by WordPress
     * @param string|array $path
     */
    public function filterTemplates($file)
    {
        $views = $this->app['sage.finder']->locate($file);

        return $views;
    }

    public function loadView($file)
    {
        $view = $this->app['sage.view'] = $this->app['view.finder']->getPossibleViewNameFromPath($file);

        /** gather data to be passed to view */
        $this->app['sage.data'] = array_reduce(get_body_class(), function ($data, $class) use ($view, $file) {
            return apply_filters("sage/template/{$class}/data", $data, $view, $file);
        }, []);

        return get_template_directory() . '/index.php';
    }

    /**
     * Get the IoC container instance.
     *
     * @return \Illuminate\Container\Container
     */
    public function getContainer()
    {
        return $this->app;
    }

    /**
     * Set the IoC container instance.
     *
     * @param \Illuminate\Container\Container $app
     */
    public function setContainer(Container $app)
    {
        $this->app = $app;
    }
}
