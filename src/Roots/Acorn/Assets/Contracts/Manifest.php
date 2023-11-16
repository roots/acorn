<?php

namespace Roots\Acorn\Assets\Contracts;

interface Manifest
{
    /**
     * Get an asset object from the Manifest
     *
     * @param  string  $key
     */
    public function asset($key): Asset;

    /**
     * Get an asset bundle from the Manifest
     *
     * @param  string  $key
     */
    public function bundle($key): Bundle;
}
