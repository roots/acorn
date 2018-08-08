<?php

namespace Roots\Acorn\Assets;

use Countable;
use ArrayAccess;
use Traversable;
use ArrayIterator;
use JsonSerializable;
use IteratorAggregate;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

class Manifest implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Arrayable, Jsonable
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
     * @param iterable $manifest
     * @param string   $uri      URI to assets root
     * @param string   $path     Path to assets root
     */
    public function __construct(iterable $manifest = [], $uri = '', $path = '')
    {
        $this->manifest = $manifest instanceof Arrayable ? $manifest->toArray() : (array) $manifest;
        $this->uri      = $uri;
        $this->path     = $path;
    }

    /** {@inheritdoc} */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->manifest);
    }

    /** {@inheritdoc} */
    public function offsetGet($key)
    {
        return $this->manifest[$key];
    }

    /** {@inheritdoc} */
    public function offsetSet($key, $value)
    {
        $this->manifest[$key] = $value;
    }

    /** {@inheritdoc} */
    public function offsetUnset($key)
    {
        unset($this->manifest[$key]);
    }

    /** {@inheritdoc} */
    public function count()
    {
        return count($this->manifest);
    }

    /** {@inheritdoc} */
    public function getIterator()
    {
        return new ArrayIterator($this->manifest);
    }

    /** {@inheritdoc} */
    public function jsonSerialize()
    {
        return $this->manifest;
    }

    /** {@inheritdoc} */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /** {@inheritdoc} */
    public function toArray()
    {
        return $this->manifest;
    }

    /**
     * Assets root URI
     *
     * @return string
     */
    public function uri()
    {
        return $this->uri;
    }

    /**
     * Assets root path
     *
     * @return string
     */
    public function path()
    {
        return $this->path;
    }

    /**
     * Decode JSON manifest
     *
     * @param  string $jsonManifest Path to .json file
     * @param  string $uri          URI to assets root
     * @param  string $path         Path to assets root
     * @return static
     */
    public static function fromJson($jsonManifest, $uri = '', $path = '')
    {
        $manifest = file_exists($jsonManifest) ? json_decode(file_get_contents($jsonManifest), true) : [];

        return new static($manifest, $uri, $path);
    }
}
