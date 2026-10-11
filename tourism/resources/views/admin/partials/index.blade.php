<div class="k-admin-heading">
    <h1>{{ $heading }}</h1>
    <a class="k-btn k-btn-ink" href="{{ $create }}">{{ __('admin.create') }}</a>
</div>
@include('admin.partials.filters')
@if ($records->isEmpty())
    <x-empty-state :title="__('admin.empty')" />
@else
    <div class="k-table-wrap">
        <table class="k-table">
            <thead>
                <tr>
                    <th>{{ __('admin.title') }}</th>
                    <th>{{ __('admin.publication') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $record)
                    <tr>
                        <td><a href="{{ route($edit, $record) }}">{{ $label($record) }}</a></td>
                        <td>{{ $record->is_published ? __('admin.published') : __('admin.draft') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $records->links() }}
@endif
