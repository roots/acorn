<?php

namespace Roots\Acorn\Pagination;

use Illuminate\Pagination\LengthAwarePaginator as LengthAwarePaginatorBase;
use Illuminate\Support\Collection;
use Roots\Acorn\Pagination\Concerns\FollowsPermalinkStructure;

class LengthAwarePaginator extends LengthAwarePaginatorBase
{
    use FollowsPermalinkStructure;

    /**
     * Get the paginator links as a collection (for JSON responses).
     *
     * Laravel labels the previous and next links with `__()`,
     * which is WordPress's version, so they're translated here.
     *
     * @return Collection
     */
    public function linkCollection()
    {
        $links = parent::linkCollection();

        $links->put(0, array_merge($links->first(), ['label' => trans('pagination.previous')]));
        $links->put($links->keys()->last(), array_merge($links->last(), ['label' => trans('pagination.next')]));

        return $links;
    }
}
