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
                    <a class="k-pager-link @if ($page == $paginator->currentPage()) is-current @endif" href="{{ $url }}" @if ($page == $paginator->currentPage()) aria-current="page" @endif>{{ $page }}</a>
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
