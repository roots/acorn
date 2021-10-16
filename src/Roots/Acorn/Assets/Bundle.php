<?php

namespace Roots\Acorn\Assets;

use Illuminate\Support\Collection;
use Roots\Acorn\Assets\Concerns\Enqueuable;
use Roots\Acorn\Assets\Contracts\Bundle as BundleContract;

class Bundle implements BundleContract
{
    use Enqueuable;

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
        $this->runtime = $bundle['js']['runtime'] ?? $bundle['js']["runtime~{$id}"] ?? null;

        unset($bundle['js']['runtime'], $bundle['js']["runtime~{$id}"]);

        $this->bundle = $bundle;
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
        if (! $callable) {
            return collect($this->bundle['css']);
        }

        collect($this->bundle['css'] ?? [])
            ->each(function ($src, $handle) use ($callable) {
                $callable("{$this->id}/{$handle}", "{$this->uri}/{$src}");
            });

        return $this;
    }

    /**
     * Get JS files in bundle.
     *
     * Optionally pass a function to execute on each JS file.
     *
     * @param callable $callable
     * @return Collection|$this
     */
    public function js(?callable $callable = null)
    {
        if (! $callable) {
            return collect($this->bundle['js']);
        }

        collect($this->bundle['js'])
            ->reject('runtime')
            ->each(function ($src, $handle) use ($callable) {
                $callable("{$this->id}/{$handle}", "{$this->uri}/{$src}", $this->dependencies());
            });

        return $this;
    }

    /**
     * Get depdencies.
     *
     * @return array
     */
    public function dependencies()
    {
        return $this->bundle['dependencies'];
    }

    /**
     * Get bundle runtime.
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
}
