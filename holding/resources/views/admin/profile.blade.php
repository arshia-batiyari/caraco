@extends('layouts.admin')

@section('title', __('admin.profile'))

@section('content')
    <h1>{{ __('admin.profile') }}</h1>
    <form class="k-form" method="post" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="k-field">
            <label for="company_name">{{ __('admin.company_name') }}</label>
            <input id="company_name" name="company_name" value="{{ old('company_name', $companyName) }}">
        </div>
        <div data-locale-tabs>
            <div class="k-tabs" role="tablist">
                @foreach (config('karako.locales') as $code => $meta)
                    <button type="button" role="tab" data-locale-tab="{{ $code }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $meta['native'] }}</button>
                @endforeach
            </div>
            @foreach (config('karako.locales') as $code => $meta)
                <div class="k-locale-panel" data-locale-panel="{{ $code }}" @if (! $loop->first) hidden @endif>
                    <div class="k-field">
                        <label for="summary-{{ $code }}">{{ __('admin.summary') }}</label>
                        <textarea id="summary-{{ $code }}" name="translations[{{ $code }}][summary]" rows="5">{{ old('translations.'.$code.'.summary', $translations->get($code)?->summary) }}</textarea>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="k-field">
            <label for="og_image">{{ __('admin.og_image') }}</label>
            @if ($ogImage)
                <img class="k-admin-preview" src="{{ \App\Support\ImageStore::url($ogImage) }}" alt="">
            @endif
            <input id="og_image" name="og_image" type="file" accept="image/jpeg,image/png,image/webp">
            @if ($ogImage)
                <label class="k-check"><input type="checkbox" name="remove_og" value="1"> {{ __('admin.remove_image') }}</label>
            @endif
        </div>
        <button class="k-btn k-btn-ink" type="submit">{{ __('admin.save') }}</button>
    </form>
@endsection
