@props(['items' => []])

<nav class="k-crumbs" aria-label="{{ __('ui.breadcrumb') }}">
    <ol class="k-wrap">
        @foreach ($items as $item)
            <li>
                @if (! empty($item['url']) && ! $loop->last)
                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                @else
                    <span aria-current="page">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
