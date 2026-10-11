<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ config('karako.locales.'.app()->getLocale().'.dir', 'rtl') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('brand/favicon.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('brand/apple-touch-icon.png') }}">
    <title>{{ __('admin.login') }} — KARAKO</title>
    <meta name="robots" content="noindex, nofollow">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="k-admin">
    <main class="k-admin-auth">
        <p class="k-logo-plate"><x-logo size="login" /></p>
        <h1>{{ __('admin.login') }}</h1>
        <form class="k-form" method="post" action="{{ route('admin.login.store') }}">
            @csrf
            @if ($errors->any())
                <x-alert type="error">{{ $errors->first() }}</x-alert>
            @endif
            <div class="k-field {{ $errors->has('email') ? 'is-invalid' : '' }}">
                <label for="email">{{ __('ui.fields.email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required>
            </div>
            <div class="k-field {{ $errors->has('password') ? 'is-invalid' : '' }}">
                <label for="password">{{ __('admin.password') }}</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
            </div>
            <label class="k-check">
                <input type="checkbox" name="remember" value="1">
                <span>{{ __('admin.remember') }}</span>
            </label>
            <button class="k-btn k-btn-ink" type="submit">{{ __('admin.login') }}</button>
        </form>
    </main>
</body>
</html>
