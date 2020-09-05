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
        $templates = [];

        // TODO: This should be cacheable, perhaps via `wp acorn` command
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

        // NOTE: We collect $_templates, not $templates.
        return collect($_templates)
            ->merge($templates[$post_type] ?? [])
            ->unique()
            ->toArray();
    }
}
