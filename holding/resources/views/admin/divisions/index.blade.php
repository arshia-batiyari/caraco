@extends('layouts.admin')

@section('title', __('admin.divisions'))

@section('content')
    <h1>{{ __('admin.divisions') }}</h1>
    @include('admin.partials.filters')
    <p class="k-field-hint">{{ __('admin.reorder_hint') }}</p>
    @if ($divisions->isEmpty())
        <x-empty-state :title="__('admin.empty')" />
    @else
        <form method="post" action="{{ route('admin.divisions.reorder') }}">
            @csrf
            @method('PUT')
            <div class="k-table-wrap">
                <table class="k-table">
                    <thead>
                        <tr>
                            <th>{{ __('admin.position') }}</th>
                            <th>{{ __('admin.title') }}</th>
                            <th>{{ __('admin.published') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($divisions as $division)
                            <tr>
                                <td>
                                    <input name="divisions[{{ $division->id }}][position]" type="number" min="1" max="99" value="{{ old('divisions.'.$division->id.'.position', $division->position) }}">
                                </td>
                                <td>{{ $division->translated()?->title ?: $division->key }}</td>
                                <td>
                                    <input type="hidden" name="divisions[{{ $division->id }}][is_published]" value="0">
                                    <input type="checkbox" name="divisions[{{ $division->id }}][is_published]" value="1" @checked(filter_var(old('divisions.'.$division->id.'.is_published', $division->is_published ? '1' : '0'), FILTER_VALIDATE_BOOL))>
                                </td>
                                <td><a href="{{ route('admin.divisions.edit', $division) }}">{{ __('admin.edit') }}</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button class="k-btn k-btn-ink" type="submit">{{ __('admin.save') }}</button>
        </form>
        {{ $divisions->links() }}
    @endif
@endsection
