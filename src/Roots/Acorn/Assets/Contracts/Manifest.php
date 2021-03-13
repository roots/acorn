<?php

namespace Roots\Acorn\Assets\Contracts;

interface Manifest
{
    /**
     * Get an asset object from the Manifest
     *
     * @param string $key
     * @return Asset
     */
    public function asset($key): Asset;

    /**
     * Get an asset bundle from the Manifest
     *
     * @param string $key
     * @return Bundle
     */
    public function bundle($key): Bundle;
}
