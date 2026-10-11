<a class="k-admin-brand" href="{{ route('admin.dashboard') }}">
    <span class="k-brand-name" translate="no">KARAKO</span>
    <span class="k-brand-line">{{ __('admin.panel') }}</span>
</a>
<nav aria-label="{{ __('admin.panel') }}">
    <h2>{{ __('admin.groups.overview') }}</h2>
    <a href="{{ route('admin.dashboard') }}" @class(['is-current' => request()->routeIs('admin.dashboard')])>{{ __('admin.dashboard') }}</a>
    <a href="{{ route('home', ['locale' => config('app.locale')]) }}">{{ __('admin.view_site') }}</a>

    <h2>{{ __('admin.groups.site') }}</h2>
    <a href="{{ route('admin.homepage.edit') }}" @class(['is-current' => request()->routeIs('admin.homepage.*')])>{{ __('admin.homepage') }}</a>
    <a href="{{ route('admin.pages.index') }}" @class(['is-current' => request()->routeIs('admin.pages.*')])>{{ __('admin.pages') }}</a>
    <a href="{{ route('admin.faqs.index') }}" @class(['is-current' => request()->routeIs('admin.faqs.*')])>{{ __('admin.faqs') }}</a>

    <h2>{{ __('admin.groups.catalog') }}</h2>
    <a href="{{ route('admin.tours.index') }}" @class(['is-current' => request()->routeIs('admin.tours.*')])>{{ __('admin.tours') }}</a>
    <a href="{{ route('admin.destinations.index') }}" @class(['is-current' => request()->routeIs('admin.destinations.*')])>{{ __('admin.destinations') }}</a>
    <a href="{{ route('admin.categories.index') }}" @class(['is-current' => request()->routeIs('admin.categories.*')])>{{ __('admin.categories') }}</a>
    <a href="{{ route('admin.styles.index') }}" @class(['is-current' => request()->routeIs('admin.styles.*')])>{{ __('admin.styles') }}</a>
    <a href="{{ route('admin.transports.index') }}" @class(['is-current' => request()->routeIs('admin.transports.*')])>{{ __('admin.transports') }}</a>
    <a href="{{ route('admin.services.index') }}" @class(['is-current' => request()->routeIs('admin.services.*')])>{{ __('admin.services') }}</a>
    <a href="{{ route('admin.hotels.index') }}" @class(['is-current' => request()->routeIs('admin.hotels.*')])>{{ __('admin.hotels') }}</a>

    <h2>{{ __('admin.medical') }}</h2>
    <a href="{{ route('admin.medical.home') }}" @class(['is-current' => request()->routeIs('admin.medical.*') && ! request()->routeIs('admin.medical.inquiries.*')])>{{ __('admin.medical') }}</a>

    <h2>{{ __('admin.groups.places') }}</h2>
    <a href="{{ route('admin.attractions.index') }}" @class(['is-current' => request()->routeIs('admin.attractions.*')])>{{ __('admin.attractions') }}</a>
    <a href="{{ route('admin.attraction-categories.index') }}" @class(['is-current' => request()->routeIs('admin.attraction-categories.*')])>{{ __('admin.attraction_categories') }}</a>

    <h2>{{ __('admin.groups.messages') }}</h2>
    <a href="{{ route('admin.inquiries.index') }}" @class(['is-current' => request()->routeIs('admin.inquiries.*')])>{{ __('admin.inquiries') }}</a>
    <a href="{{ route('admin.medical.inquiries.index') }}" @class(['is-current' => request()->routeIs('admin.medical.inquiries.*')])>{{ __('admin.medical_inquiries') }}</a>
    <a href="{{ route('admin.feedback.index') }}" @class(['is-current' => request()->routeIs('admin.feedback.*')])>{{ __('admin.feedback') }}</a>
    <a href="{{ route('admin.surveys.index') }}" @class(['is-current' => request()->routeIs('admin.surveys.*')])>{{ __('admin.surveys') }}</a>

    @if (auth()->user()?->isAdmin())
        <h2>{{ __('admin.groups.access') }}</h2>
        <a href="{{ route('admin.users.index') }}" @class(['is-current' => request()->routeIs('admin.users.*')])>{{ __('admin.users') }}</a>
    @endif
</nav>
