<?php

namespace Roots\Acorn\Assets;

use Illuminate\Support\Arr;
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
        $this->bundle = $bundle;
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
        if (! $callable) {
            return collect($this->bundle['css']);
        }

        collect($this->bundle['css'] ?? [])
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
                $callable("{$this->id}/{$handle}", $this->getUrl($src), $this->dependencies());
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

    protected function getUrl($path)
    {
        if (parse_url($path, PHP_URL_HOST)) {
            return $path;
        }

        $path = ltrim($path, '/');
        $uri = rtrim($this->uri, '/');

        return "{$uri}/{$path}";
    }

    protected function setRuntime()
    {
        if (Arr::isAssoc($this->bundle['js'])) {
            $this->runtime = $this->bundle['js']['runtime'] ?? $this->bundle['js']["runtime~{$this->id}"] ?? null;
            unset($this->bundle['js']['runtime'], $this->bundle['js']["runtime~{$this->id}"]);
        } elseif (isset($this->bundle['js'][0]) && strpos($this->bundle['js'][0], 'runtime') === 0) {
            $this->runtime = $this->bundle['js'][0];
            unset($this->bundle['js'][0]);
        }
    }
}
