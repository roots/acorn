<?php

namespace Roots\Acorn\Assets;

use Illuminate\Support\Str;
use Roots\Acorn\Assets\Contracts\Asset as AssetContract;

class Asset implements AssetContract
{
    /**
     * Local path
     *
     * @var string
     */
    protected $path;

    /**
     * Remote URI
     *
     * @var string
     */
    protected $uri;

    /**
     * Get asset from manifest
     *
     * @param  string $path Local path
     * @param  string $uri Remote URI
     */
    public function __construct(string $path, string $uri)
    {
        $this->path = Str::before($path, '?');
        $this->uri = $uri;
    }

    /** {@inheritdoc} */
    public function uri(): string
    {
        return $this->uri;
    }

    /** {@inheritdoc} */
    public function path(): string
    {
        return $this->path;
    }

    /** {@inheritdoc} */
    public function exists(): bool
    {
        return file_exists($this->path());
    }

    /** {@inheritdoc} */
    public function contents(): string
    {
        if (! $this->exists()) {
            return false;
        }

        return file_get_contents($this->path());
    }

    /**
     * Get the returned value of the asset
     *
     * @return mixed|false
     */
    public function get()
    {
        if (! $this->exists()) {
            return false;
        }

        return include $this->path();
    }

    /** {@inheritdoc} */
    public function __toString()
    {
        return $this->uri();
    }
}
