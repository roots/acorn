<?php

namespace Roots\Acorn\Sage;

use function Roots\add_filters;
use function Roots\view;
use Illuminate\Contracts\Container\Container as ContainerContract;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Str;
use Roots\Acorn\Filesystem\Filesystem;
use Roots\Acorn\Sage\ViewFinder;
use Roots\Acorn\View\FileViewFinder;

class Sage
{
    /**
     * The container implementation.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $app;

    /**
     * The ViewFinder instance.
     *
     * @var \Roots\Acorn\Sage\ViewFinder
     */
    protected $sageFinder;

    /**
     * The FileViewFinder instance.
     *
     * @var \Roots\Acorn\View\FileViewFinder
     */
    protected $fileFinder;

    /**
     * The View Factory instance.
     *
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $view;

    /**
     * Creates a new Sage instance.
     *
     * @param Filesystem        $files
     * @param ViewFinder        $sageFinder
     * @param FileViewFinder    $fileFinder
     * @param ViewFactory       $view
     * @param ContainerContract $app
     */
    public function __construct(
        Filesystem $files,
        ViewFinder $sageFinder,
        FileViewFinder $fileFinder,
        ViewFactory $view,
        ContainerContract $app
    ) {
        $this->app = $app;
        $this->files = $files;
        $this->fileFinder = $fileFinder;
        $this->sageFinder = $sageFinder;
        $this->view = $view;
    }

    /**
     * Initialize and attach Sage features.
     *
     * @return void
     */
    public function attach()
    {
        $this->attachPostVariable();
        $this->attachCommentsTemplateFilter();
        $this->attachSearchFormFilter();
        $this->attachBodyClassFilter();
        $this->attachTemplateHierarchyFilters();
        $this->attachTemplateIncludeFilter();
        $this->attachThemeTemplatesFilter();
    }

    /**
     * Attach global `$post` variable to Blade views.
     *
     * @param  WP_Post $post
     * @return void
     */
    public function filterPost($post)
    {
        $this->view->share('post', $post);
    }

    /**
     * Attach the post filter.
     *
     * @param  integer $priority
     * @return void
     */
    protected function attachPostFilter($priority = 10)
    {
        add_action('the_post', [$this, 'filterPost'], $priority);
    }

    /**
     * Search for a compiled Blade partial when resolving the comments template.
     *
     * @param  string $file
     * @return string
     */
    public function filterCommentsTemplate($file)
    {
        if (Str::startsWith($file, [STYLESHEETPATH, TEMPLATEPATH])) {
            $file = ltrim(str_replace([STYLESHEETPATH, TEMPLATEPATH], '', $file), '\\/');
        }

        if ($file == 'comments.php') {
            $file = 'partials/comments.blade.php';
        }

        return view(
            $this->fileFinder->getPossibleViewNameFromPath($file)
        )->makeLoader();
    }

    /**
     * Attach the comments template filter.
     *
     * @param  integer $priority
     * @return void
     */
    protected function attachCommentsTemplateFilter($priority = 100)
    {
        add_filter('comments_template', [$this, 'filterCommentsTemplate'], $priority);
    }

    /**
     * Use `forms/search.blade.php` for the search form.
     *
     * @return string
     */
    public function filterSearchForm()
    {
        return view('forms.search');
    }

    /**
     * Attach the search form filter.
     *
     * @param  integer $priority
     * @return void
     */
    protected function attachSearchFormFilter($priority = 100)
    {
        add_filter('get_search_form', [$this, 'filterSearchForm'], $priority);
    }

    /**
     * Clean up the body class and append the page slug.
     *
     * @param  array $classes
     * @return array
     */
    public function filterBodyClass($classes)
    {
        $classes = collect($classes);

        if (is_single() || is_page() && ! is_front_page()) {
            if (! $classes->contains($class = basename(get_permalink()))) {
                $classes->push($class);
            }
        }

        return $classes->map(function ($class) {
            return preg_replace(['/-blade(-php)?$/', '/^page-template-views/'], '', $class);
        })
        ->filter()
        ->all();
    }

    /**
     * Attach the body class filter.
     *
     * @param  integer $priority
     * @return void
     */
    protected function attachBodyClassFilter($priority = 10)
    {
        add_filter('body_class', [$this, 'filterBodyClass'], $priority);
    }

    /**
     * Use compiled Blade view when returning a template.
     *
     * @param  array $files
     * @return void
     */
    public function filterTemplateHierarchy($files)
    {
        return $this->sageFinder->locate($files);
    }

    /**
     * Use compiled Blade view when returning a template.
     *
     * @param  integer $priority
     * @return void
     */
    protected function attachTemplateHierarchyFilters($priority = 10)
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
        ], [$this, 'filterTemplateHierarchy'], $priority);
    }

    /**
     * Include compiled Blade view with data attached.
     *
     * @param  string
     * @return string
     */
    public function filterTemplateInclude($file)
    {
        $view = $this->fileFinder
            ->getPossibleViewNameFromPath(realpath($file));

        /** Gather data to be passed to view */
        $data = array_reduce(get_body_class(), function ($data, $class) use ($view, $file) {
            return apply_filters("sage/template/{$class}/data", $data, $view, $file);
        }, []);

        $this->app['sage.view'] = $view;
        $this->app['sage.data'] = $data;

        return get_template_directory() . '/index.php';
    }

    /**
     * Attach the template include filter.
     *
     * @param  integer $priority
     * @return void
     */
    protected function attachTemplateIncludeFilter($priority = 100)
    {
        add_filter('template_include', [$this, 'filterTemplateInclude'], $priority);
    }

    /**
     * Add Blade compatability for post and page templates.
     *
     * @return void
     */
    public function filterThemeTemplates($_templates, $_theme, $_post, $post_type)
    {
        $templates = [];

        foreach (array_reverse($this->fileFinder->getPaths()) as $path) {
            /**
             * We use the exact same technique as WordPress core for detecting template files.
             *
             * Caveat: we go infinite levels deep within the views folder.
             *
             * @see \WP_Theme::get_post_templates()
             * @link https://github.com/WordPress/WordPress/blob/5.2.1/wp-includes/class-wp-theme.php#L1146-L1164
             */
            foreach ($this->files->glob("{$path}/**.php") as $full_path) {
                if (! preg_match('|Template Name:(.*)$|mi', file_get_contents($full_path), $header)) {
                    continue;
                }

                $types = ['page'];

                if (preg_match('|Template Post Type:(.*)$|mi', file_get_contents($full_path), $type)) {
                    $types = explode(',', _cleanup_header_comment($type[1]));
                }

                $file = $this->files->getRelativePath("{$path}/", $full_path);

                foreach ($types as $type) {
                    $type = sanitize_key($type);

                    if (! isset($templates[$type])) {
                        $templates[$type] = [];
                    }

                    $templates[$type][$file] = _cleanup_header_comment($header[1]);
                }
            }
        }

        return collect($_templates)
            ->merge($templates[$post_type] ?? [])
            ->unique()
            ->toArray();
    }

    /**
     * Attach the theme templates filter.
     *
     * @param  integer $priority
     * @return void
     */
    protected function attachThemeTemplatesFilter($priority = 100)
    {
        add_filter('theme_templates', [$this, 'filterThemeTemplates'], $priority, 4);
    }
}
