<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Destination;
use App\Models\Faq;
use App\Models\FeedbackSubmission;
use App\Models\Hotel;
use App\Models\Page;
use App\Models\Service;
use App\Models\Survey;
use App\Models\Tour;
use App\Models\TransportOption;
use App\Models\TravelStyle;
use App\Models\User;
use App\Policies\CatalogPolicy;
use App\Policies\FeedbackPolicy;
use App\Policies\SurveyPolicy;
use App\View\SiteComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('pagination.karako');
        Paginator::defaultSimpleView('pagination.karako');
        View::composer('layouts.site', SiteComposer::class);

        Gate::define('edit-content', function (User $user): bool {
            return in_array($user->role, ['admin', 'editor'], true);
        });

        foreach ([
            Tour::class,
            Destination::class,
            Category::class,
            TravelStyle::class,
            TransportOption::class,
            Service::class,
            Hotel::class,
            Faq::class,
            Page::class,
        ] as $model) {
            Gate::policy($model, CatalogPolicy::class);
        }

        Gate::policy(FeedbackSubmission::class, FeedbackPolicy::class);
        Gate::policy(Survey::class, SurveyPolicy::class);
    }
}
