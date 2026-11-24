<?php

namespace Roots\Acorn\Pagination;

use Illuminate\Pagination\CursorPaginator as CursorPaginatorBase;
use Roots\Acorn\Pagination\Concerns\FollowsPermalinkStructure;

class CursorPaginator extends CursorPaginatorBase
{
    use FollowsPermalinkStructure;
}
