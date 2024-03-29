<?php

namespace Roots\Acorn\Assets\Concerns;

use Illuminate\Support\Str;
use Roots\Acorn\Filesystem\Filesystem;

trait Enqueuable
{
    /**
     * Resolved inline sources.
     *
     * @var array
     */
    protected static $inlined = [];

    /**
     * Get JS files in bundle.
     *
     * Optionally pass a function to execute on each JS file.
     *
     * @return Collection|$this
     */
    abstract public function js(?callable $callable = null);

    /**
     * Get CSS files in bundle.
     *
     * Optionally pass a function to execute on each CSS file.
     *
     * @return Collection|$this
     */
    abstract public function css(?callable $callable = null);

    abstract public function runtime();

    abstract public function runtimeSource();

    /**
     * Enqueue CSS files in WordPress.
     *
     * @return $this
     */
    public function enqueueCss(string $media = 'all', array $dependencies = [])
    {
        $this->css(function ($handle, $src) use (&$dependencies, $media) {
            wp_enqueue_style($handle, $src, $dependencies, null, $media);
            $this->mergeDependencies($dependencies, [$handle]);
        });

        return $this;
    }

    /**
     * Enqueue JS files in WordPress.
     *
     * @return $this
     */
    public function enqueueJs(bool|array $args = true, array $dependencies = [])
    {
        $this->js(function ($handle, $src, $bundleDependencies) use (&$dependencies, $args) {
            $this->mergeDependencies($dependencies, $bundleDependencies);

            wp_enqueue_script($handle, $src, $dependencies, null, $args);

            $this->inlineRuntime();

            $this->mergeDependencies($dependencies, [$handle]);
        });

        return $this;
    }

    /**
     * Enqueue JS and CSS files in WordPress.
     *
     * @return $this
     */
    public function enqueue()
    {
        return $this->enqueueCss()->enqueueJs();
    }

    /**
     * Add CSS files as editor styles in WordPress.
     *
     * @return $this
     */
    public function editorStyles()
    {
        $relativePath = (new Filesystem)->getRelativePath(
            Str::finish(get_theme_file_path(), '/'),
            $this->path
        );

        $this->css(function ($handle, $src) use ($relativePath) {
            if (! Str::startsWith($src, $this->uri)) {
                return add_editor_style($src);
            }

            $style = Str::of($src)
                ->after($this->uri)
                ->ltrim('/')
                ->start("{$relativePath}/")
                ->toString();

            add_editor_style($style);
        });

        return $this;
    }

    /**
     * Dequeue CSS files in WordPress.
     *
     * @return $this
     */
    public function dequeueCss()
    {
        $this->css(function ($handle) {
            wp_dequeue_style($handle);
        });

        return $this;
    }

    /**
     * Dequeue JS files in WordPress.
     *
     * @return $this
     */
    public function dequeueJs()
    {
        $this->js(function ($handle) {
            wp_dequeue_script($handle);
        });

        return $this;
    }

    /**
     * Dequeue JS and CSS files in WordPress.
     *
     * @return $this
     */
    public function dequeue()
    {
        return $this->dequeueCss()->dequeueJs();
    }

    /**
     * Inline runtime.js in WordPress.
     *
     * @return $this
     */
    public function inlineRuntime()
    {
        if (! $runtime = $this->runtime()) {
            return $this;
        }

        if (isset(self::$inlined[$runtime])) {
            return $this;
        }

        if ($contents = $this->runtimeSource()) {
            $this->inline($contents, 'before');
        }

        self::$inlined[$runtime] = $contents;

        return $this;
    }

    /**
     * Add an inline script before or after the bundle loads
     *
     * @param  string  $contents
     * @param  string  $position
     * @return $this
     */
    public function inline($contents, $position = 'after')
    {
        if (! $handles = array_keys($this->js()->keys()->toArray())) {
            return $this;
        }

        $handle = "{$this->id}/".(
            $position === 'after'
                ? array_pop($handles)
                : array_shift($handles)
        );

        wp_add_inline_script($handle, $contents, $position);

        return $this;
    }

    /**
     * Add localization data to be used by the bundle
     *
     * @param  string  $name
     * @param  array  $object
     * @return $this
     */
    public function localize($name, $object)
    {
        if (! $handles = $this->js()->keys()->toArray()) {
            return $this;
        }

        $handle = "{$this->id}/{$handles[0]}";
        wp_localize_script($handle, $name, $object);

        return $this;
    }

    /**
     * Add script translations to be used by the bundle
     *
     * @param  string  $domain
     * @param  string  $path
     * @return $this
     */
    public function translate($domain = null, $path = null)
    {
        $domain ??= wp_get_theme()->get('TextDomain');
        $path ??= lang_path();

        $this->js()->keys()->each(function ($handle) use ($domain, $path) {
            wp_set_script_translations("{$this->id}/{$handle}", $domain, $path);
        });

        return $this;
    }

    /**
     * Merge two or more arrays.
     *
     * @return void
     */
    protected function mergeDependencies(array &$dependencies, array ...$moreDependencies)
    {
        $dependencies = array_unique(array_merge($dependencies, ...$moreDependencies));
    }

    /**
     * Reset inlined sources.
     *
     * @internal
     *
     * @return void
     */
    public static function resetInlinedSources()
    {
        self::$inlined = [];
    }
}
