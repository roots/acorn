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
    /** @var \Illuminate\Contracts\Container\Container */
    protected $app;

    /** @var \Roots\Acorn\Sage\ViewFinder */
    protected $sage_finder;

    /** @var \Roots\Acorn\View\FileViewFinder */
    protected $file_finder;

    /** @var \Illuminate\Contracts\View\Factory */
    protected $view;

    public function __construct(
        Filesystem $files,
        ViewFinder $sage_finder,
        FileViewFinder $file_finder,
        ViewFactory $view,
        ContainerContract $app
    ) {
        $this->app = $app;
        $this->files = $files;
        $this->file_finder = $file_finder;
        $this->sage_finder = $sage_finder;
        $this->view = $view;
    }

    public function attach()
    {
        $this->attachPostVariable();
        $this->attachCommentsTemplateResolver();
        $this->attachTemplateHierarchyFilters();
        $this->attachTemplateIncludeFilter();
        $this->attachThemeTemplateFilter();
    }

    public function attachPostVariable($priority = 10)
    {
        add_action('the_post', function ($post) {
            $this->view->share('post', $post);
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
        ], [$this, 'filterTemplateHierarchy'], $priority);
    }

    public function attachTemplateIncludeFilter($priority = 100)
    {
        add_filter('template_include', [$this, 'loadView'], $priority);
    }

    public function attachThemeTemplateFilter($priority = 100)
    {
        add_filter('theme_templates', function ($post_templates, $_theme, $_post, $post_type) {
            $theme_templates = $this->themeTemplates();

            return collect($post_templates)
                ->merge($theme_templates[$post_type] ?? [])
                ->unique()
                ->toArray();
        }, $priority, 4);
    }

    public function themeTemplates()
    {
        $post_templates = [];

        foreach (array_reverse($this->file_finder->getPaths()) as $path) {
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
                    if (! isset($post_templates[ $type ])) {
                        $post_templates[$type] = [];
                    }

                    $post_templates[$type][$file] = _cleanup_header_comment($header[1]);
                }
            }
        }

        return $post_templates;
    }

    /**
     * Transforms path to comments template into view name
     *
     * @internal Used by WordPress
     * @param string $file Malformed path to comments template
     * @return string Fully qualified path to view loader
     */
    public function commentsTemplateResolver($file)
    {
        if (Str::startsWith($file, [STYLESHEETPATH, TEMPLATEPATH])) {
            $file = ltrim(str_replace([STYLESHEETPATH, TEMPLATEPATH], '', $file), '\\/');
        }
        $view = $this->file_finder->getPossibleViewNameFromPath($file);
        return view($view)->makeLoader();
    }

    /**
     * Template Hierarchy should search for .blade.php files
     *
     * @internal Used by WordPress
     * @param string|array $path
     */
    public function filterTemplateHierarchy($files)
    {
        $views = $this->sage_finder->locate($files);

        return $views;
    }

    public function loadView($file)
    {
        $view = $this->file_finder
            ->getPossibleViewNameFromPath(realpath($file));

        /** gather data to be passed to view */
        $data = array_reduce(get_body_class(), function ($data, $class) use ($view, $file) {
            return apply_filters("sage/template/{$class}/data", $data, $view, $file);
        }, []);

        $this->app['sage.view'] = $view;
        $this->app['sage.data'] = $data;

        return get_template_directory() . '/index.php';
    }
}
