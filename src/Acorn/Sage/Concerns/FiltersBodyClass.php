<?php

namespace Roots\Acorn\Sage\Concerns;

trait FiltersBodyClass
{
    /**
     * Clean up the body class and append the page slug.
     *
     * Filter: body_class
     *
     * @param  array $classes
     * @return array
     */
    public function filterBodyClass($classes)
    {
        $classes = collect($classes);

        if (is_single() || is_page() && ! is_front_page()) {
            if (! $classes->containsStrict($class = basename(get_permalink()))) {
                $classes->push($class);
            }
        }

        return $classes->map(function ($class) {
            return preg_replace(['/-blade(-php)?$/', '/^page-template-views/'], '', $class);
        })
        ->filter()
        ->all();
    }
}
