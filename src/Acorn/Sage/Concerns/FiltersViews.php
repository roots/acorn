<?php

namespace Roots\Acorn\Sage\Concerns;

use Illuminate\Support\Str;

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
        if (Str::startsWith($file, [STYLESHEETPATH, TEMPLATEPATH])) {
            $file = ltrim(str_replace([STYLESHEETPATH, TEMPLATEPATH], '', $file), '\\/');
        }

        if ($file === 'comments.php') {
            $file = 'partials/comments.blade.php';
        }

        return view(
            $this->fileFinder->getPossibleViewNameFromPath($file)
        )->makeLoader();
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
