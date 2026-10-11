<a class="k-admin-brand" href="{{ route('admin.dashboard') }}">
    <span class="k-brand-name" translate="no">KARAKO</span>
    <span class="k-brand-line">{{ __('admin.panel') }}</span>
</a>
<nav aria-label="{{ __('admin.panel') }}">
    <h2>{{ __('admin.groups.overview') }}</h2>
    <a href="{{ route('admin.dashboard') }}" @class(['is-current' => request()->routeIs('admin.dashboard')])>{{ __('admin.dashboard') }}</a>
    <a href="{{ route('home', ['locale' => config('app.locale')]) }}">{{ __('admin.view_site') }}</a>

    <h2>{{ __('admin.groups.site') }}</h2>
    <a href="{{ route('admin.profile.edit') }}" @class(['is-current' => request()->routeIs('admin.profile.*')])>{{ __('admin.profile') }}</a>
    <a href="{{ route('admin.homepage.edit') }}" @class(['is-current' => request()->routeIs('admin.homepage.*')])>{{ __('admin.homepage') }}</a>
    <a href="{{ route('admin.navigation.edit') }}" @class(['is-current' => request()->routeIs('admin.navigation.*')])>{{ __('admin.navigation') }}</a>
    <a href="{{ route('admin.contact.edit') }}" @class(['is-current' => request()->routeIs('admin.contact.*')])>{{ __('admin.contact') }}</a>

    <h2>{{ __('admin.groups.content') }}</h2>
    <a href="{{ route('admin.divisions.index') }}" @class(['is-current' => request()->routeIs('admin.divisions.*')])>{{ __('admin.divisions') }}</a>
    <a href="{{ route('admin.projects.index') }}" @class(['is-current' => request()->routeIs('admin.projects.*')])>{{ __('admin.projects') }}</a>
    <a href="{{ route('admin.pages.index') }}" @class(['is-current' => request()->routeIs('admin.pages.*')])>{{ __('admin.pages') }}</a>

    <h2>{{ __('admin.groups.messages') }}</h2>
    <a href="{{ route('admin.inquiries.index') }}" @class(['is-current' => request()->routeIs('admin.inquiries.*')])>{{ __('admin.inquiries') }}</a>

    @if (auth()->user()?->isAdmin())
        <h2>{{ __('admin.groups.access') }}</h2>
        <a href="{{ route('admin.users.index') }}" @class(['is-current' => request()->routeIs('admin.users.*')])>{{ __('admin.users') }}</a>
    @endif
</nav>
