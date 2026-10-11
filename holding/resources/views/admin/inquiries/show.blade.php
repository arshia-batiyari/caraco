@extends('layouts.admin')

@section('title', $inquiry->name)

@section('content')
    <p><a href="{{ route('admin.inquiries.index') }}">{{ __('admin.inquiries') }}</a></p>
    <h1>{{ $inquiry->name }}</h1>
    <dl class="k-admin-meta">
        <div><dt>{{ __('ui.fields.email') }}</dt><dd>{{ $inquiry->email }}</dd></div>
        <div><dt>{{ __('admin.locale') }}</dt><dd>{{ $inquiry->locale }}</dd></div>
        <div><dt>{{ __('admin.received') }}</dt><dd>{{ $inquiry->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</dd></div>
    </dl>
    <div class="k-prose">
        <p>{!! nl2br(e($inquiry->message)) !!}</p>
    </div>
@endsection
