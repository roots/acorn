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
            $this->inlineRuntime($handle);
            $this->mergeDependencies($dependencies, $bundle_dependencies);

            wp_enqueue_script($handle, $src, $dependencies, null, $in_footer);

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
     * Inline runtime.js in WordPress.
     *
     * @param mixed $handle
     * @return void
     */
    public function inlineRuntime($handle)
    {
        if (! $runtime = $this->runtime()) {
            return;
        }

        if (isset(self::$inlined[$runtime])) {
            return;
        }

        if ($contents = $this->runtimeSource()) {
            wp_add_inline_script($handle, $contents, 'before');
        }

        self::$inlined[$runtime] = $contents;
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
