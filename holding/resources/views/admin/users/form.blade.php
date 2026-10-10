@extends('layouts.admin')

@section('title', $account->exists ? $account->name : __('admin.create'))

@section('content')
    <p><a href="{{ route('admin.users.index') }}">{{ __('admin.users') }}</a></p>
    <h1>{{ $account->exists ? $account->name : __('admin.create') }}</h1>
    <form class="k-form" method="post" action="{{ $account->exists ? route('admin.users.update', $account) : route('admin.users.store') }}">
        @csrf
        @if ($account->exists)
            @method('PUT')
        @endif
        <div class="k-field">
            <label for="name">{{ __('ui.fields.name') }}</label>
            <input id="name" name="name" value="{{ old('name', $account->name) }}" required>
        </div>
        <div class="k-field">
            <label for="email">{{ __('ui.fields.email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email', $account->email) }}" required>
        </div>
        <div class="k-field">
            <label for="password">{{ __('admin.password') }}</label>
            <input id="password" name="password" type="password" autocomplete="new-password" @unless($account->exists) required @endunless>
        </div>
        <div class="k-field">
            <label for="role">{{ __('admin.role') }}</label>
            <select id="role" name="role">
                @foreach (['admin', 'editor'] as $role)
                    <option value="{{ $role }}" @selected(old('role', $account->role) === $role)>{{ __('admin.roles.'.$role) }}</option>
                @endforeach
            </select>
            @if ($errors->has('role'))
                <p class="k-field-error">{{ $errors->first('role') }}</p>
            @endif
        </div>
        <button class="k-btn k-btn-ink" type="submit">{{ __('admin.save') }}</button>
    </form>
@endsection
