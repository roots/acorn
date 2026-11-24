<nav role="navigation" aria-label="{{ __('Pagination Navigation') }}">
    <span>{!! __('pagination.previous') !!}</span>
    <button type="button" wire:click="gotoPage(2, '{{ $paginator->getPageName() }}')" aria-label="{{ __('Go to page :page', ['page' => 2]) }}">2</button>
    <span>{!! __('pagination.next') !!}</span>
</nav>
