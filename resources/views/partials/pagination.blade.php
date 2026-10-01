{{--
  Hand-rolled rather than $paginator->links(): Laravel's bundled views assume
  Tailwind or Bootstrap, and this app ships neither.
--}}
@if ($paginator->hasPages())
  <div class="pager">
    <span>
      Showing {{ $paginator->firstItem() }}&ndash;{{ $paginator->lastItem() }}
      of {{ $paginator->total() }}
      &middot; page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
    </span>
    <span class="links">
      @if ($paginator->onFirstPage())
        <span class="off">&larr; Previous</span>
      @else
        <a href="{{ $paginator->previousPageUrl() }}" rel="prev">&larr; Previous</a>
      @endif

      @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" rel="next">Next &rarr;</a>
      @else
        <span class="off">Next &rarr;</span>
      @endif
    </span>
  </div>
@endif
