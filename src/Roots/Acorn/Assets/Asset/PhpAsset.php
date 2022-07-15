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
    public function requireOnce()
    {
        $this->assertExists();
        return require_once $this->path();
    }

    /**
     * Get the returned value of the asset
     *
     * @return mixed
     */
    public function require()
    {
        $this->assertExists();
        return require $this->path();
    }

    /**
     * Get the returned value of the asset
     *
     * @return mixed
     */
    public function includeOnce()
    {
        $this->assertExists();
        return include_once $this->path();
    }

    /**
     * Get the returned value of the asset
     *
     * @return mixed
     */
    public function include()
    {
        $this->assertExists();
        return include $this->path();
    }

    /**
     * Assert that the asset exists.
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function assertExists()
    {
        if (! $this->exists()) {
            throw new FileNotFoundException("Asset [{$this->path()}] not found.");
        }
    }
}
