<?php

namespace Roots\Acorn\View\Composers\Concerns;

use Illuminate\Support\Fluent;

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
        $acf_data = \get_fields($post_id);

        return array_map(function ($value, $key) {
            $value = is_array($value) ? new Fluent($value) : $value;
            return method_exists($this, $key) ? $this->{$key}($value) : $value;
        }, $acf_data, array_keys($acf_data));
    }
}
