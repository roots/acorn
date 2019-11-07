<?php

namespace Roots\Acorn\Assets;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Str;
use Roots\Acorn\Assets\Asset;
use Roots\Acorn\Assets\Contracts\Asset as AssetContract;
use Roots\Acorn\Assets\Contracts\Manifest as ManifestContract;

class RelativePathManifest implements
    Arrayable,
    \ArrayAccess,
    \Countable,
    \IteratorAggregate,
    Jsonable,
    \JsonSerializable,
    ManifestContract
{
    /** @var array */
    protected $manifest;

    /** @var string */
    protected $path;

    /** @var string */
    protected $uri;

    /**
     * Manifest constructor
     *
     * @param  string $path
     * @param  string $uri
     * @param  iterable|\Illuminate\Contracts\Support\Arrayable $manifest
     * @return void
     */
    public function __construct(string $path, string $uri, $manifest = [])
    {
        $this->path = $path;
        $this->uri = $uri;
        $manifest = $manifest instanceof Arrayable ? $manifest->toArray() : (array) $manifest;

        foreach ($manifest as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Normalizes the key and value before setting them
     *
     * @return void
     */
    public function set($original, $revved): void
    {
        $this->manifest[$this->normalizeRelativePath($original)] = $this->normalizeRelativePath($revved);
    }

    /**
     * Get an asset object from the Manifest
     *
     * @param string $key Key for locating asset within the manifest
     * @return \Roots\Acorn\Assets\Asset
     */
    public function get($key): AssetContract
    {
        $key = $this->normalizeRelativePath($key);
        $relative_path = $this->manifest[$key] ?? $key;
        return new Asset("{$this->path}/{$relative_path}", "{$this->uri}/{$relative_path}");
    }

    /**
     * Normalizes to forward slashes and removes leading slash.
     *
     * @return string
     */
    protected function normalizeRelativePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        return ltrim($path, '/');
    }


    /** {@inheritdoc} */
    public function offsetExists($key): bool
    {
        return array_key_exists($key, $this->manifest);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Roots\Acorn\Assets\Asset
     */
    public function offsetGet($key): Asset
    {
        return $this->get($key);
    }

    /** {@inheritdoc} */
    public function offsetSet($key, $value): void
    {
        $this->set($key, $value);
    }

    /** {@inheritdoc} */
    public function offsetUnset($key): void
    {
        unset($this->manifest[$key]);
    }

    /** {@inheritdoc} */
    public function count(): int
    {
        return count($this->manifest);
    }

    /** {@inheritdoc} */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->manifest);
    }

    /** {@inheritdoc} */
    public function jsonSerialize()
    {
        return $this->manifest;
    }

    /** {@inheritdoc} */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /** {@inheritdoc} */
    public function toArray(): array
    {
        return $this->manifest;
    }

    /**
     * Assets root URI
     *
     * @return string
     */
    public function uri(): string
    {
        return $this->uri;
    }

    /**
     * Assets root path
     *
     * @return string
     */
    public function path(): string
    {
        return $this->path;
    }
}
