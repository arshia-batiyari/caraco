@extends('layouts.admin')

@section('content')
    <p><a href="{{ route('admin.feedback.index') }}">{{ __('admin.feedback') }}</a></p>
    <h1>{{ $submission->title }}</h1>
    <dl class="k-admin-meta">
        <div><dt>{{ __('admin.type') }}</dt><dd>{{ __('ui.feedback.types.'.$submission->type) }}</dd></div>
        <div><dt>{{ __('ui.fields.name') }}</dt><dd>{{ $submission->is_anonymous ? __('ui.feedback.anonymous') : ($submission->name ?: '—') }}</dd></div>
        @if ($submission->email)<div><dt>{{ __('ui.fields.email') }}</dt><dd>{{ $submission->email }}</dd></div>@endif
        @if ($submission->phone)<div><dt>{{ __('ui.fields.phone') }}</dt><dd>{{ $submission->phone }}</dd></div>@endif
        <div><dt>{{ __('ui.fields.language') }}</dt><dd>{{ $submission->preferred_language }}</dd></div>
        <div><dt>{{ __('ui.feedback.original_language') }}</dt><dd>{{ $submission->locale }}</dd></div>
        @foreach ([
            'rating_overall' => 'ui.feedback.rating_overall',
            'rating_accommodation' => 'ui.feedback.rating_accommodation',
            'rating_transport' => 'ui.feedback.rating_transport',
            'rating_organization' => 'ui.feedback.rating_organization',
            'rating_service' => 'ui.feedback.rating_service',
        ] as $field => $label)
            @if ($submission->{$field})<div><dt>{{ __($label) }}</dt><dd>{{ $submission->{$field} }}/5</dd></div>@endif
        @endforeach
        @if ($submission->tour)<div><dt>{{ __('ui.fields.tour') }}</dt><dd>{{ $submission->tour->translations->first()?->title }}</dd></div>@endif
        @if ($submission->service)<div><dt>{{ __('ui.feedback.service') }}</dt><dd>{{ $submission->service->translations->first()?->name }}</dd></div>@endif
        <div><dt>{{ __('ui.fields.message') }}</dt><dd>{{ $submission->message }}</dd></div>
    </dl>

    <form class="k-form" method="post" action="{{ route('admin.feedback.update', $submission) }}">
        @csrf
        @method('PUT')
        @if ($submission->review && $submission->canBePublic())
            <div class="k-field">
                <label for="status">{{ __('admin.publication') }}</label>
                <select id="status" name="status">
                    @foreach (['pending', 'approved', 'rejected', 'hidden'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $submission->review->status) === $status)>{{ __('admin.review_statuses.'.$status) }}</option>
                    @endforeach
                </select>
            </div>
            <label class="k-check">
                <input type="hidden" name="is_verified" value="0">
                <input type="checkbox" name="is_verified" value="1" @checked(filter_var(old('is_verified', $submission->review->is_verified), FILTER_VALIDATE_BOOL))>
                <span>{{ __('admin.verified') }}</span>
            </label>
            <p class="k-hint">{{ __('admin.verified_hint') }}</p>
        @else
            <p class="k-hint">{{ __('admin.complaint_private') }}</p>
            <div class="k-field">
                <label for="complaint-status">{{ __('admin.status') }}</label>
                <select id="complaint-status" name="complaint_status">
                    @foreach (['new', 'reviewing', 'awaiting', 'resolved', 'closed'] as $status)
                        <option value="{{ $status }}" @selected(old('complaint_status', $submission->followUps->last()?->complaint_status ?? 'new') === $status)>{{ __('admin.complaint_statuses.'.$status) }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="k-field">
            <label for="note">{{ __('admin.note') }}</label>
            <textarea id="note" name="note" rows="4">{{ old('note') }}</textarea>
        </div>
        <div class="k-field">
            <label for="response">{{ __('admin.recorded_reply') }}</label>
            <textarea id="response" name="response" rows="4">{{ old('response') }}</textarea>
            <p class="k-hint">{{ __('admin.recorded_reply_hint') }}</p>
        </div>
        <button class="k-btn k-btn-ink" type="submit">{{ __('admin.save') }}</button>
    </form>

    @if ($submission->followUps->isNotEmpty())
        <h2>{{ __('admin.follow_ups') }}</h2>
        <ol class="k-admin-list">
            @foreach ($submission->followUps as $followUp)
                <li>
                    <time datetime="{{ $followUp->created_at?->toDateTimeString() }}">{{ $followUp->created_at?->toDateTimeString() }}</time>
                    @if ($followUp->complaint_status) — {{ __('admin.complaint_statuses.'.$followUp->complaint_status) }} @endif
                    @if ($followUp->author) — {{ $followUp->author->name }} @endif
                    @if ($followUp->note)<p>{{ $followUp->note }}</p>@endif
                    @if ($followUp->response)<p>{{ $followUp->response }}</p>@endif
                </li>
            @endforeach
        </ol>
    @endif

    @if (auth()->user()?->isAdmin())
        <form class="k-admin-delete" method="post" action="{{ route('admin.feedback.destroy', $submission) }}">
            @csrf
            @method('DELETE')
            <button class="k-btn k-btn-line" type="submit">{{ __('admin.delete') }}</button>
        </form>
    @endif
@endsection
