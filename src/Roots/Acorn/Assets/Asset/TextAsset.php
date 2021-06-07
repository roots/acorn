<?php

namespace Roots\Acorn\Assets\Asset;

class TextAsset extends Asset
{
    /**
     * Character encoding
     *
     * @var string
     */
    protected $charset;

    /**
     * Get character encoding.
     *
     * @param string $fallback Fallback if charset cannot be determined
     * @return string
     */
    public function charset($fallback = 'UTF-8'): string
    {
        if ($this->charset) {
            return $this->charset;
        }

        if (preg_match('//u', $this->contents())) {
            return $this->charset = 'UTF-8';
        }

        if (function_exists('mb_detect_encoding')) {
            return $this->charset = mb_detect_encoding($this->contents()) ?: $fallback;
        }

        return $this->charset = $fallback;
    }

    /**
     * Get data URL of asset.
     *
     * @param string $mediatype MIME content type
     * @param string $charset Character encoding
     * @param string $urlencode List of characters to be percent-encoded
     * @return string
     */
    public function dataUrl(?string $mediatype = null, ?string $charset = null, string $urlencode = '%\'"'): string
    {
        if ($this->dataUrl) {
            return $this->dataUrl;
        }

        if (! $mediatype) {
            $mediatype = $this->contentType();
        }

        if (! strstr($mediatype, 'charset')) {
            $mediatype .= ';charset=' . ($charset ?: $this->charset());
        }

        $percents = [];
        foreach (preg_split('//u', $urlencode, -1, PREG_SPLIT_NO_EMPTY) as $char) {
            $percents[$char] = rawurlencode($char);
        }

        $data = strtr($this->contents(), $percents);

        return $this->dataUrl = "data:{$mediatype},{$data}";
    }

    /**
     * Get data URL of asset.
     *
     * @param string $mediatype MIME content type
     * @param string $charset Character encoding
     * @param string $urlencode List of characters to be percent-encoded
     * @return string
     */
    public function dataUri(?string $mediatype = null, ?string $charset = null, string $urlencode = '%\'"'): string
    {
        return $this->dataUrl($mediatype, $charset, $urlencode);
    }
}
