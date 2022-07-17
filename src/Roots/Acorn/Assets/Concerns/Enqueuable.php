<?php

namespace Roots\Acorn\Assets\Concerns;

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
     * @param callable $callable
     * @return Collection|$this
     */
    abstract public function js(?callable $callable = null);

    /**
     * Get CSS files in bundle.
     *
     * Optionally pass a function to execute on each CSS file.
     *
     * @param callable $callable
     * @return Collection|$this
     */
    abstract public function css(?callable $callable = null);

    abstract public function runtime();

    abstract public function runtimeSource();

    /**
     * Enqueue CSS files in WordPress.
     *
     * @param string $media
     * @param array $dependencies
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
     * @param bool $in_footer
     * @param array $dependencies
     * @return $this
     */
    public function enqueueJs(bool $in_footer = true, array $dependencies = [])
    {
        $this->js(function ($handle, $src, $bundle_dependencies) use (&$dependencies, $in_footer) {
            $this->mergeDependencies($dependencies, $bundle_dependencies);

            wp_enqueue_script($handle, $src, $dependencies, null, $in_footer);

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
     * @param string $contents
     * @param string $position
     * @return $this
     */
    public function inline($contents, $position = 'after')
    {
        if (! $handles = array_keys($this->js()->keys()->toArray())) {
            return $this;
        }

        $handle = "{$this->id}/" . (
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
     * @param string $name
     * @param array $object
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
     * Merge two or more arrays.
     *
     * @param array $dependencies
     * @param array $more_dependencies
     * @return void
     */
    protected function mergeDependencies(array &$dependencies, array ...$more_dependencies)
    {
        $dependencies = array_unique(array_merge($dependencies, ...$more_dependencies));
    }

    /**
     * Reset inlined sources.
     *
     * @internal
     * @return void
     */
    public static function resetInlinedSources()
    {
        self::$inlined = [];
    }
}
