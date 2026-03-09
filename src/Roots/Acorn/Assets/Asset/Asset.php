<?php

namespace Roots\Acorn\Assets\Asset;

use Roots\Acorn\Assets\Contracts\Asset as AssetContract;
use Roots\Acorn\Filesystem\Filesystem;
use SplFileInfo;

class Asset implements AssetContract
{
    /**
     * The local asset path.
     *
     * @var string
     */
    protected $path;

    /**
     * The remote asset URI.
     *
     * @var string
     */
    protected $uri;

    /**
     * The asset MIME content type.
     *
     * @var string
     */
    protected $type;

    /**
     * The asset base64-encoded contents.
     *
     * @var string
     */
    protected $base64;

    /**
     * The asset data URL.
     *
     * @var string
     */
    protected $dataUrl;

    /**
     * Get asset from manifest
     *
     * @param  string  $path  Local path
     * @param  string  $uri  Remote URI
     */
    public function __construct(string $path, string $uri)
    {
        $this->path = $path;
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
            return '';
        }

        return file_get_contents($this->path());
    }

    /**
     * Get the relative path to the asset.
     *
     * @param  string  $basePath  Base path to use for relative path.
     */
    public function relativePath(string $basePath): string
    {
        $basePath = rtrim($basePath, '/\\').'/';

        return (new Filesystem)->getRelativePath($basePath, $this->path());
    }

    /**
     * Get the base64-encoded contents of the asset.
     *
     * @return string
     */
    public function base64()
    {
        if ($this->base64) {
            return $this->base64;
        }

        return $this->base64 = base64_encode($this->contents());
    }

    /**
     * Get data URL of asset.
     *
     * @param  string  $mediatype  MIME content type
     */
    public function dataUrl(?string $mediatype = null): string
    {
        if ($this->dataUrl) {
            return $this->dataUrl;
        }

        if (! $mediatype) {
            $mediatype = $this->contentType();
        }

        return $this->dataUrl = "data:{$mediatype};base64,{$this->base64()}";
    }

    /**
     * Get data URL of asset.
     *
     * @param  string  $mediatype  MIME content type
     */
    public function dataUri(?string $mediatype = null): string
    {
        return $this->dataUrl($mediatype);
    }

    /**
     * Get the MIME content type.
     *
     * @return string|false
     */
    public function contentType()
    {
        if ($this->type) {
            return $this->type;
        }

        return $this->type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->path());
    }

    /**
     * Get the MIME content type.
     *
     * @return string|false
     */
    public function mimeType()
    {
        return $this->contentType();
    }

    /**
     * Get SplFileInfo instance of asset.
     *
     * @return SplFileInfo
     */
    public function file()
    {
        return new SplFileInfo($this->path());
    }

    /** {@inheritdoc} */
    public function __toString()
    {
        return $this->uri();
    }
}
