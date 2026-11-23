<?php

namespace Roots\Acorn\Pagination;

use Illuminate\Pagination\Paginator as PaginatorBase;
use Roots\Acorn\Pagination\Concerns\FollowsPermalinkStructure;

class Paginator extends PaginatorBase
{
    use FollowsPermalinkStructure;
}
