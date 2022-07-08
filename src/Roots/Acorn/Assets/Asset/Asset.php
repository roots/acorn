<?php

namespace Roots\Acorn\Assets\Asset;

use SplFileInfo;
use Roots\Acorn\Assets\Contracts\Asset as AssetContract;
use Roots\Acorn\Filesystem\Filesystem;

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
     * MIME Content Type
     *
     * @var string
     */
    protected $type;

    /**
     * Base64-encoded contents
     *
     * @var string
     */
    protected $base64;

    /**
     * Data URL of asset.
     *
     * @var string
     */
    protected $dataUrl;

    /**
     * Get asset from manifest
     *
     * @param  string $path Local path
     * @param  string $uri Remote URI
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
     * @param string $base_path Base path to use for relative path.
     * @return string
     */
    public function relativePath(string $base_path): string
    {
        $base_path = rtrim($base_path, '/\\') . '/';

        return (new Filesystem())->getRelativePath($base_path, $this->path());
    }

    /**
     * Base64-encoded contents
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
     * @param string $mediatype MIME content type
     * @return string
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
     * @param string $mediatype MIME content type
     * @return string
     */
    public function dataUri(?string $mediatype = null): string
    {
        return $this->dataUrl($mediatype);
    }

    /**
     * Get the MIME content type
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
     * Get the MIME content type
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
