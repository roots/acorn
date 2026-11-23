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
            ! function_exists('wp_is_block_theme')
            || ! wp_is_block_theme()
            || ! current_theme_supports('block-templates')
        ) {
            return [...$templates, ...$files];
        }

        $pages = [];

        if ($template = get_page_template_slug()) {
            $pages = array_filter($templates, fn ($file) => str_contains($file, $template));

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
     * Restore Blade views that WordPress refused to locate.
     *
     * WordPress' `locate_template()` rejects any template name containing `..`
     * unless it resolves inside the theme directory (see
     * `_wp_is_template_path_allowed()`, shipped in the 7.1.2 security release
     * and backported to older branches). Blade views that live outside the
     * theme (e.g. Radicle, or a Bedrock project with views at the project root)
     * are emitted as `../` relative paths and would otherwise be silently
     * skipped, leaving WordPress to fall back to the theme's `index.php` shim,
     * which then renders itself recursively.
     *
     * Walk the hierarchy in order and accept the first existing candidate that
     * either resolves inside a registered view path (a Blade view) or is the
     * template WordPress already located. When WordPress picked a block
     * template instead, defer to {@see filterBlockTemplate()}.
     *
     * Filter: {type}_template
     *
     * @param  string  $template
     * @param  string  $type
     * @param  string[]  $templates
     * @return string
     */
    public function filterTemplate($template, $type, $templates)
    {
        if ($template === ABSPATH . WPINC . '/template-canvas.php') {
            return $this->filterBlockTemplate($template, $templates);
        }

        return $this->sageFinder->resolve($templates, $template ? realpath($template) : false) ?? $template;
    }

    /**
     * Prefer a Blade view over a less specific block template.
     *
     * `locate_block_template()` only considers block templates at least as
     * specific as the PHP template WordPress located. Because WordPress no
     * longer locates Blade views outside the theme, it falls through to the
     * theme's `index.php` and a block template of any specificity wins, e.g.
     * `templates/index.html` over `home.blade.php`. Restore the Blade view when
     * one sits higher in the hierarchy than the block template WordPress chose.
     *
     * @param  string  $template
     * @param  string[]  $templates
     * @return string
     */
    protected function filterBlockTemplate($template, $templates)
    {
        global $_wp_current_template_id;

        if (! $_wp_current_template_id) {
            return $template;
        }

        $slug = Str::after($_wp_current_template_id, '//');
        $position = array_search($slug, array_map('_strip_template_file_suffix', $templates), true);

        if ($position === false) {
            return $template;
        }

        $view = $this->sageFinder->resolve(array_slice($templates, 0, $position));

        if (! $view) {
            return $template;
        }

        $this->discardBlockTemplate();

        return $view;
    }

    /**
     * Undo the state `locate_block_template()` prepared for `template-canvas.php`.
     *
     * @return void
     */
    protected function discardBlockTemplate()
    {
        global $_wp_current_template_id, $_wp_current_template_content;

        $_wp_current_template_id = null;
        $_wp_current_template_content = null;

        remove_action('wp_head', '_block_template_viewport_meta_tag', 0);

        if (remove_action('wp_head', '_block_template_render_title_tag', 1)) {
            add_action('wp_head', '_wp_render_title_tag', 1);
        }
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
        $view = $this->fileFinder->getPossibleViewNameFromPath($file = realpath($file));

        $view = trim($view, '\\/.');

        /** Gather data to be passed to view */
        $data = array_reduce(
            get_body_class(),
            fn ($data, $class) => apply_filters("sage/template/{$class}/data", $data, $view, $file),
            [],
        );

        $this->app['sage.view'] = $this->view->exists($view) ? $view : $file;
        $this->app['sage.data'] = $data;

        return get_template_directory() . '/index.php';
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
            foreach (array_filter(
                $this->files->allFiles($path),
                fn ($file) => $file->getExtension() === 'php',
            ) as $fullPath) {
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
