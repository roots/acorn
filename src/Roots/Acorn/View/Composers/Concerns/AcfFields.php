<?php

namespace Roots\Acorn\View\Composers\Concerns;

use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

trait AcfFields
{
    /**
     * ACF data to be passed to the view before rendering.
     *
     * @param  int $post_id
     * @return array
     */
    protected function fields($post_id = null)
    {
        return collect(\get_fields($post_id))
            ->mapWithKeys(function ($value, $key) {
                $value = is_array($value) ? new Fluent($value) : $value;
                $method = Str::camel($key);
                return [$key => method_exists($this, $method) ? $this->{$method}($value) : $value];
            })->all();
    }
}
