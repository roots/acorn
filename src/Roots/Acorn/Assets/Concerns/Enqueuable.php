<?php

namespace Roots\Acorn\Assets\Concerns;

trait Enqueuable
{
    protected static array $inlined = [];

    abstract public function js(?callable $callable = null);

    abstract public function css(?callable $callable = null);

    abstract public function runtime();

    abstract public function runtimeSource();

    public function enqueueCss(string $media = 'all')
    {
        $this->css(function ($handle, $src) use ($media) {
            wp_enqueue_style($handle, $src, [], null, $media);
        });

        return $this;
    }

    public function enqueueJs(bool $in_footer = true, array $dependencies = [])
    {
        $this->js(function ($handle, $src, $bundle_dependencies) use (&$dependencies, $in_footer) {
            $this->enqueueRuntime($handle);
            $this->mergeDependencies($dependencies, $bundle_dependencies);

            wp_enqueue_script($handle, $src, $dependencies, null, $in_footer);

            $this->mergeDependencies($dependencies, [$handle]);
        });

        return $this;
    }

    public function enqueueRuntime($handle)
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

    protected function mergeDependencies(array &$dependencies, array ...$more_dependencies)
    {
        $dependencies = array_unique(array_merge($dependencies, ...$more_dependencies));
    }

    public static function resetInlinedSources()
    {
        self::$inlined = [];
    }
}
