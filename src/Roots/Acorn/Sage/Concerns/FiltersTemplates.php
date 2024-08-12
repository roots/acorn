<?php

namespace Roots\Acorn\Sage\Concerns;

use Illuminate\Support\Str;

trait FiltersTemplates
{
    /**
     * Use compiled Blade view when returning a template.
     *
     * Filter: {type}_template_hierarchy
     *
     * @param  array  $files
     * @return string[] List of possible views
     */
    public function filterTemplateHierarchy($files)
    {
        $templates = $this->sageFinder->locate($files);

        if (
            ! function_exists('wp_is_block_theme') ||
            ! wp_is_block_theme() ||
            ! current_theme_supports('block-templates')
        ) {
            return [...$templates, ...$files];
        }

        $pages = [];

        if ($template = get_page_template_slug()) {
            $pages = array_filter(
                $templates,
                fn ($file) => str_contains($file, $template)
            );

            $templates = array_diff($templates, $pages);
        }

        return collect([...$pages, ...$files, ...$templates])
            ->groupBy(function ($item) {
                return Str::of($item)->afterLast('/')->before('.');
            })
            ->flatten()
            ->toArray();
    }

    /**
     * Include compiled Blade view with data attached.
     *
     * Filter: template_include
     *
     * @param  string
     * @return string
     */
    public function filterTemplateInclude($file)
    {
        $view = $this->fileFinder
            ->getPossibleViewNameFromPath($file = realpath($file));

        $view = trim($view, '\\/.');

        /** Gather data to be passed to view */
        $data = array_reduce(
            get_body_class(),
            fn ($data, $class) => apply_filters("sage/template/{$class}/data", $data, $view, $file),
            []
        );

        $this->app['sage.view'] = $this->view->exists($view) ? $view : $file;
        $this->app['sage.data'] = $data;

        return get_template_directory().'/index.php';
    }

    /**
     * Add Blade compatibility for theme templates.
     *
     * NOTE: Internally, WordPress interchangeably uses "page templates" "post templates" and "theme templates"
     *
     * Filter: theme_templates
     *
     * @return string[] List of theme templates
     */
    public function filterThemeTemplates($templates, $theme, $post, $postType)
    {
        return collect($templates)
            ->merge($this->getTemplates($postType, $theme->load_textdomain() ? $theme->get('TextDomain') : ''))
            ->unique()
            ->toArray();
    }

    /**
     * We use the exact same technique as WordPress core for detecting template files.
     *
     * Caveat: we go infinite levels deep within the views folder.
     *
     * @see \WP_Theme::get_post_templates()
     * @link https://github.com/WordPress/WordPress/blob/5.8.1/wp-includes/class-wp-theme.php#L1203-L1221
     *
     * @param  string  $postType
     * @param  string  $textDomain
     * @return string[]
     */
    protected function getTemplates($postType = '', $textDomain = '')
    {
        if ($templates = wp_cache_get('acorn/post_templates', 'themes')) {
            return $templates[$postType] ?? [];
        }

        $templates = [];

        foreach (array_reverse($this->fileFinder->getPaths()) as $path) {
            foreach (
                array_filter($this->files->allFiles($path), fn ($file) => $file->getExtension() === 'php') as $fullPath
            ) {
                if (! preg_match('|Template Name:(.*)$|mi', file_get_contents($fullPath), $header)) {
                    continue;
                }

                $types = ['page'];

                if (preg_match('|Template Post Type:(.*)$|mi', file_get_contents($fullPath), $type)) {
                    $types = explode(',', _cleanup_header_comment($type[1]));
                }

                $file = $this->files->getRelativePath("{$path}/", $fullPath);

                foreach ($types as $type) {
                    $type = sanitize_key($type);

                    if (! isset($templates[$type])) {
                        $templates[$type] = [];
                    }

                    $templates[$type][$file] = _cleanup_header_comment($header[1]);
                }
            }
        }

        if ($textDomain) {
            foreach ($templates as $type => $files) {
                foreach ($files as $file => $name) {
                    $templates[$type][$file] = translate($name, $textDomain);
                }
            }
        }

        wp_cache_add('acorn/post_templates', $templates, 'themes');

        return $templates[$postType] ?? [];
    }
}
