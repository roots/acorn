<?php

namespace Roots\Acorn\Assets;

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

    public function __construct(string $id, array $bundle, string $path, string $uri = '/')
    {
        $this->id = $id;
        $this->path = $path;
        $this->uri = $uri;
        $this->runtime = $bundle['js']['runtime'] ?? $bundle['js']["runtime~{$id}"] ?? null;

        unset($bundle['js']['runtime'], $bundle['js']["runtime~{$id}"]);

        $this->bundle = $bundle;
    }

    public function css(?callable $callable = null)
    {
        if (! $callable) {
            return collect($this->bundle['css']);
        }

        collect($this->bundle['css'])
            ->each(function ($src, $handle) use ($callable) {
                $callable("{$this->id}/{$handle}", "{$this->uri}/{$src}");
            });

        return $this;
    }

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

    public function dependencies()
    {
        return $this->bundle['dependencies'];
    }

    public function runtime()
    {
        return $this->runtime;
    }

    public function runtimeSource()
    {
        if ($sauce = self::$runtimes[$runtime = $this->runtime()] ?? null) {
            return $sauce;
        }

        return self::$runtimes[$runtime] = file_get_contents("{$this->path}/{$runtime}");
    }
}
