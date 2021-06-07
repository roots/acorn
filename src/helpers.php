<?php

namespace Roots;

use Roots\Acorn\Assets\Contracts\Asset;
use Roots\Acorn\Assets\Contracts\Bundle;
use Roots\Acorn\Bootloader;

/**
 * Get asset from manifest
 *
 * @param  string $asset
 * @return Asset
 */
function asset(string $asset): Asset
{
    return app('assets.manifest')->asset($asset);
}

/**
 * Get bundle from manifest
 *
 * @param  string $bundle
 * @return Bundle
 */
function bundle(string $bundle): Bundle
{
    return app('assets.manifest')->bundle($bundle);
}

/**
 * Initialize the Acorn bootloader.
 *
 * @param  callable|null $callback
 * @return void
 */
function bootloader($callback = null)
{
    static $bootloader;

    if (! $bootloader) {
        $bootloader = new Bootloader();
    }

    if (is_callable($callback)) {
        $bootloader->call($callback);
    }
}
