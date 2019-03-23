<?php

namespace Roots\Acorn;

use Illuminate\Config\Repository as ConfigBase;

class Config extends ConfigBase
{
    /**
     * Set a given configuration value.
     *
     * @param  array|string  $key
     * @param  mixed   $value
     * @return void
     */
    public function set($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            $value = apply_filters("roots.acorn.config.{$key}", $value);
            Arr::set($this->items, $key, $value);
        }
    }
}
