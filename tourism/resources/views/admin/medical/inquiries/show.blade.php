@extends('layouts.admin')

@section('content')
    <p><a href="{{ route('admin.medical.inquiries.index') }}">{{ __('admin.medical_inquiries') }}</a></p>
    <h1>{{ $inquiry->name }}</h1>
    <dl class="k-admin-meta">
        <div><dt>{{ __('ui.fields.email') }}</dt><dd>{{ $inquiry->email }}</dd></div>
        @if ($inquiry->phone)<div><dt>{{ __('ui.fields.phone') }}</dt><dd>{{ $inquiry->phone }}</dd></div>@endif
        <div><dt>{{ __('ui.fields.language') }}</dt><dd>{{ $inquiry->preferred_language }}</dd></div>
        @if ($inquiry->country)<div><dt>{{ __('ui.medical.country') }}</dt><dd>{{ $inquiry->country }}</dd></div>@endif
        @if ($inquiry->service)<div><dt>{{ __('ui.medical.service') }}</dt><dd>{{ $inquiry->service->translations->first()?->name }}</dd></div>@endif
        <div><dt>{{ __('ui.medical.questions') }}</dt><dd>{{ $inquiry->message }}</dd></div>
    </dl>
    <form class="k-form" method="post" action="{{ route('admin.medical.inquiries.update', $inquiry) }}">
        @csrf
        @method('PUT')
        <div class="k-field">
            <label for="status">{{ __('admin.status') }}</label>
            <select id="status" name="status">
                @foreach (['new', 'reviewing', 'closed'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $inquiry->status) === $status)>{{ __('admin.statuses.'.$status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="k-field">
            <label for="admin-note">{{ __('admin.note') }}</label>
            <textarea id="admin-note" name="admin_note" rows="4">{{ old('admin_note', $inquiry->admin_note) }}</textarea>
        </div>
        <button class="k-btn k-btn-ink" type="submit">{{ __('admin.save') }}</button>
    </form>
    @can('delete', $inquiry)
        <form class="k-admin-delete" method="post" action="{{ route('admin.medical.inquiries.destroy', $inquiry) }}">@csrf @method('DELETE')<button class="k-btn k-btn-line" type="submit">{{ __('admin.delete') }}</button></form>
    @endcan
@endsection
