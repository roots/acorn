<?php

namespace Roots\Acorn\Assets;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Roots\Acorn\Assets\Concerns\Conditional;
use Roots\Acorn\Assets\Concerns\Enqueuable;
use Roots\Acorn\Assets\Contracts\Bundle as BundleContract;

class Bundle implements BundleContract
{
    use Conditional, Enqueuable;

    /**
     * The bundle ID.
     *
     * @var string
     */
    protected $id;

    /**
     * The bundle path.
     *
     * @var string
     */
    protected $path;

    /**
     * The bundle URI.
     *
     * @var string
     */
    protected $uri;

    /**
     * The bundle runtime.
     *
     * @var string|null
     */
    protected $runtime;

    /**
     * The bundle contents.
     *
     * @var array
     */
    protected $bundle;

    /**
     * The bundle runtimes.
     *
     * @var array
     */
    protected static $runtimes = [];

    /**
     * Create a new bundle.
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
        return $this->bundle['dependencies'] ?? [];
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
     * @return string
     */
    protected function getUrl(string $path)
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
            $this->runtime = $this->bundle['js']['runtime']
                ?? $this->bundle['js']["runtime~{$this->id}"]
                ?? null;

            unset($this->bundle['js']['runtime'], $this->bundle['js']["runtime~{$this->id}"]);

            return;
        }

        $this->runtime = $this->getBundleRuntime() ?? $this->getBundleRuntime('mjs');
    }

    /**
     * Retrieve the runtime in a bundle.
     *
     * @return string|null
     */
    protected function getBundleRuntime(string $type = 'js')
    {
        if (! $this->bundle[$type]) {
            return null;
        }

        foreach ($this->bundle[$type] as $key => $value) {
            if (! str_contains($value, 'runtime')) {
                continue;
            }

            unset($this->bundle[$type][$key]);

            return $value;
        }

        return null;
    }
}
