<?php

namespace Roots\Acorn\Assets\Contracts;

use Roots\Acorn\Assets\Contracts\Asset;

interface Manifest
{
    /**
     * Get an asset object from the Manifest
     *
     * @return \Roots\Acorn\Assets\Contracts\Asset
     */
    public function get($key): Asset;
}
