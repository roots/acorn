<?php

namespace Roots\Acorn\Sage\Concerns;

use function Roots\view;

trait FiltersViews
{
    /**
     * Search for a compiled Blade partial when resolving the comments template.
     *
     * Filter: comments_template
     *
     * @param  string $file
     * @return string Path to comments template
     */
    public function filterCommentsTemplate($file)
    {
        if (file_exists($file)) {
            return $file;
        }

        return view()->exists('partials.comments') ? view('partials.comments')->makeLoader() : $file;
    }

    /**
     * Use `forms/search.blade.php` for the search form.
     *
     * Filter: get_search_form
     *
     * @return string Rendered view
     */
    public function filterSearchForm($view)
    {
        return view()->exists('forms.search') ? view('forms.search') : $view;
    }
}
