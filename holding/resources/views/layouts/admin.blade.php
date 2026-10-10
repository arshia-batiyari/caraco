<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ config('karako.locales.'.app()->getLocale().'.dir', 'rtl') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('admin.dashboard')) — KARAKO</title>
    <meta name="robots" content="noindex, nofollow">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <noscript>
        <style>.k-locale-panel { display: block !important; }</style>
    </noscript>
</head>
<body class="k-admin">
    <header class="k-admin-bar">
        <div class="k-wrap-wide k-admin-bar-inner">
            <a class="k-brand" href="{{ route('admin.dashboard') }}">
                <span class="k-brand-name" translate="no">KARAKO</span>
                <span class="k-brand-line">{{ __('admin.panel') }}</span>
            </a>
            <nav class="k-admin-nav" aria-label="{{ __('admin.panel') }}">
                <a href="{{ route('admin.dashboard') }}">{{ __('admin.dashboard') }}</a>
                <a href="{{ route('admin.profile.edit') }}">{{ __('admin.profile') }}</a>
                <a href="{{ route('admin.homepage.edit') }}">{{ __('admin.homepage') }}</a>
                <a href="{{ route('admin.divisions.index') }}">{{ __('admin.divisions') }}</a>
                <a href="{{ route('admin.projects.index') }}">{{ __('admin.projects') }}</a>
                <a href="{{ route('admin.pages.index') }}">{{ __('admin.pages') }}</a>
                <a href="{{ route('admin.navigation.edit') }}">{{ __('admin.navigation') }}</a>
                <a href="{{ route('admin.contact.edit') }}">{{ __('admin.contact') }}</a>
                <a href="{{ route('admin.inquiries.index') }}">{{ __('admin.inquiries') }}</a>
                @if (auth()->user()?->isAdmin())
                    <a href="{{ route('admin.users.index') }}">{{ __('admin.users') }}</a>
                @endif
                <a href="{{ route('home', ['locale' => config('app.locale')]) }}">{{ __('admin.view_site') }}</a>
            </nav>
            <form method="post" action="{{ route('admin.logout') }}">
                @csrf
                <button class="k-btn k-btn-line" type="submit">{{ __('admin.logout') }}</button>
            </form>
        </div>
    </header>
    <main class="k-admin-main k-wrap">
        @if (session('status'))
            <x-alert type="success">{{ session('status') }}</x-alert>
        @endif
        @if ($errors->any())
            <x-alert type="error">{{ __('admin.validation.summary') }}</x-alert>
        @endif
        @yield('content')
    </main>
</body>
</html>
