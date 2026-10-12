<?php

namespace App\Providers;

use App\Models\Attraction;
use App\Models\AttractionCategory;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Faq;
use App\Models\FeedbackSubmission;
use App\Models\Hotel;
use App\Models\MedicalCategory;
use App\Models\MedicalDoctor;
use App\Models\MedicalFaq;
use App\Models\MedicalInquiry;
use App\Models\MedicalPage;
use App\Models\MedicalProvider;
use App\Models\MedicalService;
use App\Models\MedicalStep;
use App\Models\Page;
use App\Models\Service;
use App\Models\Survey;
use App\Models\Tour;
use App\Models\TransportOption;
use App\Models\TravelStyle;
use App\Models\User;
use App\Policies\CatalogPolicy;
use App\Policies\FeedbackPolicy;
use App\Policies\MedicalInquiryPolicy;
use App\Policies\SurveyPolicy;
use App\Support\EnsureAppKey;
use App\Support\SecureUrls;
use App\View\SiteComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $key = EnsureAppKey::ensure($this->app->environmentFilePath(), config('app.key'));
        config(['app.key' => $key]);

        $path = config('karako.public_path');
        if (is_string($path) && $path !== '') {
            $this->app->usePublicPath($path);
        }
    }

    public function boot(): void
    {
        // utf8mb4 indexes on shared hosts are capped at 1000 bytes. 191 * 4 = 764.
        Schema::defaultStringLength(191);

        SecureUrls::apply();
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
            MedicalPage::class,
            MedicalCategory::class,
            MedicalService::class,
            MedicalProvider::class,
            MedicalDoctor::class,
            MedicalStep::class,
            MedicalFaq::class,
            Attraction::class,
            AttractionCategory::class,
        ] as $model) {
            Gate::policy($model, CatalogPolicy::class);
        }

        Gate::policy(MedicalInquiry::class, MedicalInquiryPolicy::class);
        Gate::policy(FeedbackSubmission::class, FeedbackPolicy::class);
        Gate::policy(Survey::class, SurveyPolicy::class);
    }
}
