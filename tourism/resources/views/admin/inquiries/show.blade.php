@extends('layouts.admin')

@section('content')
    <p><a href="{{ route('admin.inquiries.index') }}">{{ __('admin.inquiries') }}</a></p>
    <h1>{{ $inquiry->name }}</h1>
    <dl class="k-admin-meta">
        <div><dt>{{ __('ui.fields.email') }}</dt><dd>{{ $inquiry->email }}</dd></div>
        @if ($inquiry->phone)<div><dt>{{ __('ui.fields.phone') }}</dt><dd>{{ $inquiry->phone }}</dd></div>@endif
        <div><dt>{{ __('ui.fields.language') }}</dt><dd>{{ $inquiry->preferred_language }}</dd></div>
        @if ($inquiry->tour)<div><dt>{{ __('ui.fields.tour') }}</dt><dd>{{ $inquiry->tour->translations->first()?->title ?? '#'.$inquiry->tour_id }}</dd></div>@endif
        @if ($inquiry->destination)<div><dt>{{ __('ui.fields.destination') }}</dt><dd>{{ $inquiry->destination->translations->first()?->name }}</dd></div>@endif
        @if ($inquiry->destination_text)<div><dt>{{ __('ui.fields.destination_text') }}</dt><dd>{{ $inquiry->destination_text }}</dd></div>@endif
        @if ($inquiry->travel_start)<div><dt>{{ __('ui.fields.travel_start') }}</dt><dd>{{ $inquiry->travel_start->toDateString() }}</dd></div>@endif
        @if ($inquiry->travel_end)<div><dt>{{ __('ui.fields.travel_end') }}</dt><dd>{{ $inquiry->travel_end->toDateString() }}</dd></div>@endif
        @if ($inquiry->travelers)<div><dt>{{ __('ui.fields.travelers') }}</dt><dd>{{ $inquiry->travelers }}</dd></div>@endif
        <div><dt>{{ __('ui.fields.message') }}</dt><dd>{{ $inquiry->message }}</dd></div>
    </dl>
    <form class="k-form" method="post" action="{{ route('admin.inquiries.update', $inquiry) }}">
        @csrf
        @method('PUT')
        <div class="k-field">
            <label for="status">{{ __('admin.status') }}</label>
            <select id="status" name="status">
                @foreach (['new', 'reviewing', 'assigned', 'closed'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $inquiry->status) === $status)>{{ __('admin.statuses.'.$status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="k-field">
            <label for="assigned-to">{{ __('admin.assignee') }}</label>
            <select id="assigned-to" name="assigned_to">
                <option value="">{{ __('ui.fields.none') }}</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected((string) old('assigned_to', $inquiry->assigned_to) === (string) $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="k-field">
            <label for="admin-note">{{ __('admin.note') }}</label>
            <textarea id="admin-note" name="admin_note" rows="4">{{ old('admin_note', $inquiry->admin_note) }}</textarea>
        </div>
        <button class="k-btn k-btn-ink" type="submit">{{ __('admin.save') }}</button>
    </form>
    @if (auth()->user()?->isAdmin())
        <form class="k-admin-delete" method="post" action="{{ route('admin.inquiries.destroy', $inquiry) }}">@csrf @method('DELETE')
            <label class="k-check"><input type="checkbox" name="confirm_delete" value="1"><span>{{ __('admin.confirm_delete') }}</span></label><button class="k-btn k-btn-line" type="submit">{{ __('admin.delete') }}</button></form>
    @endif
@endsection
