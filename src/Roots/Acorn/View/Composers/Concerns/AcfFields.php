<?php

namespace Roots\Acorn\View\Composers\Concerns;

use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

trait AcfFields
{
    /**
     * ACF data to be passed to the view before rendering.
     *
     * @param  int  $postId
     * @return array
     */
    protected function fields($postId = null)
    {
        return collect(\get_fields($postId))
            ->mapWithKeys(function ($value, $key) {
                $value = is_array($value) ? new Fluent($value) : $value;
                $method = Str::camel($key);

                return [$key => method_exists($this, $method) ? $this->{$method}($value) : $value];
            })->all();
    }
}
