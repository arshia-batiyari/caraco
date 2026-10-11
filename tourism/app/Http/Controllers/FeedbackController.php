<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedbackRequest;
use App\Models\FeedbackSubmission;
use App\Models\Service;
use App\Models\Survey;
use App\Models\Tour;
use App\Support\ContentPresenter;
use App\Support\FeedbackFilter;
use App\Support\LocaleText;
use App\Support\PublicCatalog;
use App\Support\SeoData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(Request $request, PublicCatalog $catalog, ContentPresenter $presenter): View|RedirectResponse
    {
        $filtered = FeedbackFilter::requested($request);
        $filter = FeedbackFilter::fromRequest($request);
        if ($filtered && $filter === null) {
            return redirect()->route('feedback.index')->withErrors(['filters' => __('ui.filters.invalid')]);
        }

        $query = FeedbackSubmission::query()
            ->where('type', '!=', 'complaint')
            ->whereHas('review', fn ($inner) => $inner->where('status', 'approved'));
        if ($filter) {
            $filter->apply($query);
        }

        $reviews = $query->with(['review', 'tour.translations', 'service.translations'])
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        $canonical = route('feedback.index');

        return $presenter->render('pages.feedback.index', [
            'reviews' => $reviews,
            'filters' => $filter?->values() ?? [],
            'filtered' => $filtered,
            'tours' => Tour::query()->published()->with('translations')->orderBy('position')->get(),
            'services' => Service::query()->where('is_published', true)->with('translations')->orderBy('position')->get(),
            'surveys' => Survey::query()->open()->with('translations')->orderBy('position')->get(),
            'privacy' => $catalog->privacyPage(),
            'crumbs' => [
                ['label' => __('ui.nav.home'), 'url' => route('home')],
                ['label' => __('ui.feedback.title'), 'url' => $canonical],
            ],
            'schema' => $presenter->breadcrumbs([
                ['name' => __('ui.feedback.title'), 'url' => $canonical],
            ]),
        ], new SeoData(
            title: $presenter->documentTitle(null, __('ui.feedback.title')),
            description: __('ui.feedback.description'),
            canonical: $canonical,
            robots: $filtered ? 'noindex, follow' : 'index, follow',
        ));
    }

    public function store(StoreFeedbackRequest $request): RedirectResponse
    {
        if ($request->honeypotFilled()) {
            return redirect()->route('feedback.index')->with('sent', true);
        }

        $anonymous = $request->boolean('is_anonymous');
        DB::transaction(function () use ($request, $anonymous) {
            $submission = FeedbackSubmission::query()->create([
                'type' => $request->input('type'),
                'is_anonymous' => $anonymous,
                'name' => $anonymous ? null : LocaleText::blank($request->input('name')),
                'email' => LocaleText::blank($request->input('email')),
                'phone' => LocaleText::blank($request->input('phone')),
                'wants_follow_up' => $request->boolean('wants_follow_up'),
                'tour_id' => $request->input('tour_id'),
                'service_id' => $request->input('service_id'),
                'rating_overall' => $request->input('rating_overall'),
                'rating_accommodation' => $request->input('rating_accommodation'),
                'rating_transport' => $request->input('rating_transport'),
                'rating_organization' => $request->input('rating_organization'),
                'rating_service' => $request->input('rating_service'),
                'title' => $request->input('title'),
                'message' => $request->input('message'),
                'locale' => app()->getLocale(),
                'preferred_language' => $request->input('preferred_language'),
                'consent' => true,
                'ip' => $request->ip(),
            ]);

            if ($submission->canBePublic()) {
                $submission->review()->create(['status' => 'pending']);
            } else {
                $submission->followUps()->create(['complaint_status' => 'new']);
            }
        });

        $message = $request->input('type') === 'complaint'
            ? __('ui.feedback.stored_private')
            : __('ui.feedback.stored');

        return redirect()->route('feedback.index')->with('sent', $message);
    }
}
