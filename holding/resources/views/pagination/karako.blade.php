@if ($paginator->hasPages())
    <nav class="k-pager" aria-label="{{ __('ui.pagination') }}">
        @if ($paginator->onFirstPage())
            <span class="k-pager-link is-disabled">{{ __('ui.previous') }}</span>
        @else
            <a class="k-pager-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">{{ __('ui.previous') }}</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="k-pager-link is-disabled">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="k-pager-link is-current" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="k-pager-link" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="k-pager-link" href="{{ $paginator->nextPageUrl() }}" rel="next">{{ __('ui.next') }}</a>
        @else
            <span class="k-pager-link is-disabled">{{ __('ui.next') }}</span>
        @endif
    </nav>
@endif
