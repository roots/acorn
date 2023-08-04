<?php

declare(strict_types=1);

namespace Roots\Acorn\Assets;

use Illuminate\Foundation\Vite as LaravelVite;

class Vite extends LaravelVite
{
    /**
     * {@inheritDoc}
     */
    protected function assetPath($path, $secure = null)
    {
        return \Roots\asset($path)->uri();
    }
}
