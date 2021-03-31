<?php

namespace Roots\Acorn\Assets\Asset;

use Illuminate\Contracts\Filesystem\FileNotFoundException;

class PhpAsset extends Asset
{
    /**
     * Get the returned value of the asset
     *
     * @return mixed
     */
    public function load($require = false, $once = false)
    {
        if (! $this->exists()) {
            throw new FileNotFoundException("Asset [{$this->path()}] not found.");
        }

        if ($require) {
            return $once
                ? require_once $this->path()
                : require $this->path();
        }

        return $once
            ? include_once $this->path()
            : include $this->path();
    }
}
