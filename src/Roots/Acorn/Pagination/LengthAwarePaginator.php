<?php

namespace Roots\Acorn\Pagination;

use Illuminate\Pagination\LengthAwarePaginator as LengthAwarePaginatorBase;
use Roots\Acorn\Pagination\Concerns\FollowsPermalinkStructure;

class LengthAwarePaginator extends LengthAwarePaginatorBase
{
    use FollowsPermalinkStructure;
}
