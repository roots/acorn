<?php

namespace Roots\Acorn\Assets;

use Illuminate\Foundation\Vite as FoundationVite;

class Vite extends FoundationVite
{
    /**
     * Generate an asset path for the application.
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    protected function assetPath($path, $secure = null)
    {
        return \Roots\asset($path)->uri();
    }
}
