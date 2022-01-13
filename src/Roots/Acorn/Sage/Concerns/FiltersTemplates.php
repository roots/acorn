<?php

namespace Roots\Acorn\Sage\Concerns;

trait FiltersTemplates
{
    /**
     * Use compiled Blade view when returning a template.
     *
     * Filter: {type}_template_hierarchy
     *
     * @param  array $files
     * @return string[] List of possible views
     */
    public function filterTemplateHierarchy($files)
    {
        return $this->sageFinder->locate($files);
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
        $data = array_reduce(get_body_class(), function ($data, $class) use ($view, $file) {
            return apply_filters("sage/template/{$class}/data", $data, $view, $file);
        }, []);

        $this->app['sage.view'] = $this->view->exists($view) ? $view : $file;
        $this->app['sage.data'] = $data;

        return get_template_directory() . '/index.php';
    }

    /**
     * Add Blade compatability for theme templates.
     *
     * NOTE: Internally, WordPress interchangeably uses "page templates" "post templates" and "theme templates"
     *
     * Filter: theme_templates
     *
     * @return string[] List of theme templates
     */
    public function filterThemeTemplates($_templates, $_theme, $_post, $post_type)
    {
        return collect($_templates)
            ->merge($this->getTemplates($post_type, $_theme->load_textdomain() ? $_theme->get('TextDomain') : ''))
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
     * @param string $post_type
     * @param string $text_domain
     * @return string[]
     */
    protected function getTemplates($post_type = '', $text_domain = '')
    {
        if ($templates = wp_cache_get('acorn/post_templates', 'themes')) {
            return $templates[$post_type] ?? [];
        }

        $templates = [];

        foreach (array_reverse($this->fileFinder->getPaths()) as $path) {
            foreach (
                array_filter($this->files->allFiles($path), function ($file) {
                    return $file->getExtension() === 'php';
                }) as $full_path
            ) {
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

        if ($text_domain) {
            foreach ($templates as $type => $files) {
                foreach ($files as $file => $name) {
                    $templates[$type][$file] = translate($name, $text_domain);
                }
            }
        }

        wp_cache_add('acorn/post_templates', $templates, 'themes');

        return $templates[$post_type] ?? [];
    }
}
