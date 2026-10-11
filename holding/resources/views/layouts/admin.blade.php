<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ config('karako.locales.'.app()->getLocale().'.dir', 'rtl') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('brand/favicon.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('brand/apple-touch-icon.png') }}">
    <title>@yield('title', __('admin.dashboard')) — KARAKO</title>
    <meta name="robots" content="noindex, nofollow">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <noscript>
        <style>.k-locale-panel { display: block !important; } .k-admin-side { display: block !important; }</style>
    </noscript>
</head>
<body class="k-admin">
    <input id="k-admin-nav-toggle" class="k-admin-toggle" type="checkbox">
    <div class="k-admin-shell">
        <aside class="k-admin-side">
            @include('admin.partials.nav')
        </aside>
        <div class="k-admin-content">
            <header class="k-admin-bar">
                <div class="k-wrap-wide k-admin-bar-inner">
                    <label class="k-admin-menu" for="k-admin-nav-toggle">{{ __('admin.menu') }}</label>
                    <a class="k-brand k-admin-bar-brand" href="{{ route('admin.dashboard') }}">
                        <x-logo size="admin" />
                        <span class="k-sr">{{ __('admin.panel') }}</span>
                    </a>
                    <p class="k-admin-bar-title">@yield('title', __('admin.dashboard'))</p>
                    <form method="post" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="k-btn k-btn-line" type="submit">{{ __('admin.logout') }}</button>
                    </form>
                </div>
            </header>
            <main class="k-admin-main k-wrap">
                @php $adminTitle = trim($__env->yieldContent('title')); @endphp
                <nav class="k-admin-crumbs" aria-label="{{ __('admin.menu') }}">
                    @if ($adminTitle === '' || $adminTitle === __('admin.dashboard'))
                        <span aria-current="page">{{ __('admin.dashboard') }}</span>
                    @else
                        <a href="{{ route('admin.dashboard') }}">{{ __('admin.dashboard') }}</a>
                        <span aria-hidden="true">/</span>
                        <span aria-current="page">{{ $adminTitle }}</span>
                    @endif
                </nav>
                @if (session('status'))
                    <x-alert type="success">{{ session('status') }}</x-alert>
                @endif
                @if ($errors->any())
                    <x-alert type="error">
                        {{ __('admin.validation.summary') }}
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-alert>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
