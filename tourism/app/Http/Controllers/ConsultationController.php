<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConsultationRequest;
use App\Models\Destination;
use App\Models\Inquiry;
use App\Models\Tour;
use App\Support\ContentPresenter;
use App\Support\LocaleText;
use App\Support\PublicCatalog;
use App\Support\SeoData;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    public function show(PublicCatalog $catalog, ContentPresenter $presenter): View
    {
        return $presenter->render('pages.consultation', $this->formData($catalog), new SeoData(
            title: $presenter->documentTitle(null, __('ui.consultation.title')),
            description: __('ui.consultation.lede'),
            canonical: route('consultation'),
        ), null);
    }

    public function store(StoreConsultationRequest $request, PublicCatalog $catalog): RedirectResponse
    {
        if (! $request->honeypotFilled()) {
            Inquiry::query()->create([
                'name' => $request->string('name')->trim()->toString(),
                'email' => $request->string('email')->trim()->lower()->toString(),
                'phone' => LocaleText::blank($request->input('phone')),
                'locale' => app()->getLocale(),
                'preferred_language' => $request->input('preferred_language'),
                'tour_id' => $request->input('tour_id') ?: null,
                'destination_id' => $request->input('destination_id') ?: null,
                'destination_text' => LocaleText::blank($request->input('destination_text')),
                'travel_start' => $request->input('travel_start') ?: null,
                'travel_end' => $request->input('travel_end') ?: null,
                'travelers' => $request->input('travelers') ?: null,
                'message' => $request->string('message')->trim()->toString(),
                'consent' => true,
                'status' => 'new',
                'ip_address' => $request->ip(),
            ]);
        }

        return redirect()->route('consultation')->with('sent', true);
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(PublicCatalog $catalog): array
    {
        $selected = request()->integer('tour');

        return [
            'tours' => Tour::query()->published()->with('translations')->orderBy('position')->get()
                ->filter(fn (Tour $tour) => $tour->publicTranslation() !== null)->values(),
            'destinations' => Destination::query()->where('is_published', true)->with('translations')->orderBy('position')->get()
                ->filter(fn (Destination $destination) => $destination->publicTranslation() !== null)->values(),
            'privacy' => $catalog->privacyPage(),
            'selectedTour' => $selected > 0 ? $selected : null,
            'crumbs' => [
                ['label' => __('ui.nav.home'), 'url' => route('home')],
                ['label' => __('ui.nav.consultation'), 'url' => route('consultation')],
            ],
        ];
    }
}
