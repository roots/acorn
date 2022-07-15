<?php

namespace Roots\Acorn\Assets;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Roots\Acorn\Assets\Concerns\Conditional;
use Roots\Acorn\Assets\Concerns\Enqueuable;
use Roots\Acorn\Assets\Contracts\Bundle as BundleContract;

class Bundle implements BundleContract
{
    use Enqueuable;
    use Conditional;

    protected $id;
    protected $path;
    protected $uri;
    protected $runtime;
    protected $bundle;

    protected static $runtimes = [];

    /**
     * Create a new bundle.
     *
     * @param string $id
     * @param array $bundle
     * @param string $path
     * @param string $uri
     */
    public function __construct(string $id, array $bundle, string $path, string $uri = '/')
    {
        $this->id = $id;
        $this->path = $path;
        $this->uri = $uri;
        $this->bundle = $bundle + ['js' => [], 'mjs' => [], 'css' => []];
        $this->setRuntime();
    }

    /**
     * Get CSS files in bundle.
     *
     * Optionally pass a function to execute on each CSS file.
     *
     * @param callable $callable
     * @return Collection|$this
     */
    public function css(?callable $callable = null)
    {
        $styles = $this->conditional ? $this->bundle['css'] : [];

        if (! $callable) {
            return collect($styles);
        }

        collect($styles)
            ->each(function ($src, $handle) use ($callable) {
                $callable("{$this->id}/{$handle}", $this->getUrl($src));
            });

        return $this;
    }

    /**
     * Get JS files in bundle.
     *
     * Optionally pass a function to execute on each JS file.
     *
     * @param  callable $callable
     * @return Collection|$this
     */
    public function js(?callable $callable = null)
    {
        $scripts = $this->conditional ? array_merge($this->bundle['js'], $this->bundle['mjs']) : [];

        if (! $callable) {
            return collect($scripts);
        }

        collect($scripts)
            ->reject('runtime')
            ->each(function ($src, $handle) use ($callable) {
                $callable("{$this->id}/{$handle}", $this->getUrl($src), $this->dependencies());
            });

        return $this;
    }

    /**
     * Get the bundle dependencies.
     *
     * @return array
     */
    public function dependencies()
    {
        return $this->bundle['dependencies'];
    }

    /**
     * Get the bundle runtime.
     *
     * @return string|null
     */
    public function runtime()
    {
        return $this->runtime;
    }

    /**
     * Get bundle runtime contents.
     *
     * @return string|null
     */
    public function runtimeSource()
    {
        if (($runtime = $this->runtime()) === null) {
            return null;
        }

        if ($sauce = self::$runtimes[$runtime] ?? null) {
            return $sauce;
        }

        return self::$runtimes[$runtime] = file_get_contents("{$this->path}/{$runtime}");
    }

    /**
     * Get the bundle URL.
     *
     * @param  string $path
     * @return string
     */
    protected function getUrl($path)
    {
        if (parse_url($path, PHP_URL_HOST)) {
            return $path;
        }

        $path = ltrim($path, '/');
        $uri = rtrim($this->uri, '/');

        return "{$uri}/{$path}";
    }

    /**
     * Set the bundle runtime.
     *
     * @return void
     */
    protected function setRuntime()
    {
        if (Arr::isAssoc($this->bundle['js'])) {
            $this->runtime = $this->bundle['js']['runtime'] ?? $this->bundle['js']["runtime~{$this->id}"] ?? null;
            unset($this->bundle['js']['runtime'], $this->bundle['js']["runtime~{$this->id}"]);
        } elseif (isset($this->bundle['js'][0]) && strpos($this->bundle['js'][0], 'runtime') === 0) {
            $this->runtime = $this->bundle['js'][0];
            unset($this->bundle['js'][0]);
        } elseif (isset($this->bundle['js'][0]) && strpos($this->bundle['js'][0], 'js/runtime') === 0) {
            $this->runtime = $this->bundle['js'][0];
            unset($this->bundle['js'][0]);
        } elseif (isset($this->bundle['mjs'][0]) && strpos($this->bundle['mjs'][0], 'runtime') === 0) {
            $this->runtime = $this->bundle['mjs'][0];
            unset($this->bundle['mjs'][0]);
        } elseif (isset($this->bundle['mjs'][0]) && strpos($this->bundle['mjs'][0], 'js/runtime') === 0) {
            $this->runtime = $this->bundle['mjs'][0];
            unset($this->bundle['mjs'][0]);
        }
    }
}
