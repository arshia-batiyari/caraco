<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicalInquiryRequest;
use App\Models\MedicalCategory;
use App\Models\MedicalDoctor;
use App\Models\MedicalFaq;
use App\Models\MedicalInquiry;
use App\Models\MedicalPage;
use App\Models\MedicalProvider;
use App\Models\MedicalService;
use App\Models\MedicalStep;
use App\Support\AlternateLinks;
use App\Support\ContentPresenter;
use App\Support\ImageStore;
use App\Support\LocaleText;
use App\Support\PublicCatalog;
use App\Support\PublishedRecord;
use App\Support\SeoData;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MedicalController extends Controller
{
    public function index(PublicCatalog $catalog, ContentPresenter $presenter): View
    {
        $page = MedicalPage::query()->with('translations')->first();
        $copy = $page?->translations->firstWhere('locale', app()->getLocale());

        return $presenter->render('pages.medical.index', [
            'copy' => $copy,
            'categories' => $this->cards(MedicalCategory::class),
            'services' => $this->cards(MedicalService::class, ['category.translations']),
            'providers' => $this->cards(MedicalProvider::class, ['destination.translations']),
            'steps' => $this->current(MedicalStep::class, 'title'),
            'faqs' => $this->current(MedicalFaq::class, 'question'),
            'inquiryServices' => MedicalService::query()->published()->with('translations')->orderBy('position')->get(),
            'privacy' => $catalog->privacyPage(),
            'crumbs' => [
                ['label' => __('ui.nav.home'), 'url' => route('home')],
                ['label' => __('ui.medical.title'), 'url' => route('medical.index')],
            ],
            'schema' => $presenter->breadcrumbs([
                ['name' => __('ui.medical.title'), 'url' => route('medical.index')],
            ]),
        ], new SeoData(
            title: $presenter->documentTitle($copy?->meta_title, $copy?->title ?: __('ui.medical.title')),
            description: (string) ($copy?->meta_description ?: $copy?->summary ?: __('ui.medical.description')),
            canonical: $presenter->canonical($copy?->canonical_url, route('medical.index')),
        ), AlternateLinks::section('medical.index'));
    }

    public function services(ContentPresenter $presenter): View
    {
        return $presenter->render('pages.medical.services', [
            'services' => MedicalService::query()->published()->with(['translations', 'category.translations'])->orderBy('position')->paginate(9),
            'categories' => $this->cards(MedicalCategory::class),
            'crumbs' => [
                ['label' => __('ui.nav.home'), 'url' => route('home')],
                ['label' => __('ui.medical.title'), 'url' => route('medical.index')],
                ['label' => __('ui.medical.services'), 'url' => route('medical.services')],
            ],
            'schema' => $presenter->breadcrumbs([
                ['name' => __('ui.medical.title'), 'url' => route('medical.index')],
                ['name' => __('ui.medical.services'), 'url' => route('medical.services')],
            ]),
        ], new SeoData(
            title: $presenter->documentTitle(null, __('ui.medical.services')),
            description: __('ui.medical.services_description'),
            canonical: route('medical.services'),
        ), AlternateLinks::section('medical.services'));
    }

    public function category(ContentPresenter $presenter): View
    {
        [$record, $translation] = PublishedRecord::find(MedicalCategory::class, (string) request()->route('slug'), 'name');
        $url = route('medical.categories.show', ['slug' => $translation->slug]);

        return $presenter->render('pages.medical.category', [
            'record' => $record,
            'translation' => $translation,
            'services' => MedicalService::query()->published()->where('medical_category_id', $record->id)->with('translations')->orderBy('position')->get(),
            'crumbs' => $this->crumbs($translation->name, $url),
            'schema' => $presenter->breadcrumbs([
                ['name' => __('ui.medical.title'), 'url' => route('medical.index')],
                ['name' => $translation->name, 'url' => $url],
            ]),
        ], $this->seo($presenter, $translation, 'name', $url), AlternateLinks::record($record, 'medical.categories.show', 'name'));
    }

    public function service(PublicCatalog $catalog, ContentPresenter $presenter): View
    {
        [$record, $translation] = PublishedRecord::find(MedicalService::class, (string) request()->route('slug'), 'name', [
            'category.translations',
            'images.translations',
            'providers.translations',
            'providers.destination.translations',
        ]);
        $url = route('medical.services.show', ['slug' => $translation->slug]);

        return $presenter->render('pages.medical.service', [
            'record' => $record,
            'translation' => $translation,
            'cover' => ImageStore::url($record->cover_path),
            'inquiryServices' => MedicalService::query()->published()->with('translations')->orderBy('position')->get(),
            'privacy' => $catalog->privacyPage(),
            'crumbs' => $this->crumbs($translation->name, $url),
            'schema' => $presenter->breadcrumbs([
                ['name' => __('ui.medical.title'), 'url' => route('medical.index')],
                ['name' => $translation->name, 'url' => $url],
            ]),
        ], $this->seo($presenter, $translation, 'name', $url, ImageStore::url($record->cover_path)), AlternateLinks::record($record, 'medical.services.show', 'name'));
    }

    public function provider(ContentPresenter $presenter): View
    {
        [$record, $translation] = PublishedRecord::find(MedicalProvider::class, (string) request()->route('slug'), 'name', [
            'destination.translations',
            'destination.parent.translations',
            'services.translations',
            'doctors.translations',
        ]);
        $url = route('medical.providers.show', ['slug' => $translation->slug]);

        return $presenter->render('pages.medical.provider', [
            'record' => $record,
            'translation' => $translation,
            'cover' => ImageStore::url($record->cover_path),
            'crumbs' => $this->crumbs($translation->name, $url),
            'schema' => $presenter->breadcrumbs([
                ['name' => __('ui.medical.title'), 'url' => route('medical.index')],
                ['name' => $translation->name, 'url' => $url],
            ]),
        ], $this->seo($presenter, $translation, 'name', $url, ImageStore::url($record->cover_path)), AlternateLinks::record($record, 'medical.providers.show', 'name'));
    }

    public function doctor(ContentPresenter $presenter): View
    {
        [$record, $translation] = PublishedRecord::find(MedicalDoctor::class, (string) request()->route('slug'), 'name', [
            'provider.translations',
        ]);
        $url = route('medical.doctors.show', ['slug' => $translation->slug]);

        return $presenter->render('pages.medical.doctor', [
            'record' => $record,
            'translation' => $translation,
            'image' => ImageStore::url($record->image_path),
            'crumbs' => $this->crumbs($translation->name, $url),
            'schema' => $presenter->breadcrumbs([
                ['name' => __('ui.medical.title'), 'url' => route('medical.index')],
                ['name' => $translation->name, 'url' => $url],
            ]),
        ], $this->seo($presenter, $translation, 'name', $url, ImageStore::url($record->image_path)), AlternateLinks::record($record, 'medical.doctors.show', 'name'));
    }

    public function store(StoreMedicalInquiryRequest $request): RedirectResponse
    {
        if ($request->honeypotFilled()) {
            return redirect()->route('medical.index')->with('sent', true);
        }

        MedicalInquiry::query()->create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => LocaleText::blank($request->input('phone')),
            'locale' => app()->getLocale(),
            'preferred_language' => $request->input('preferred_language'),
            'country' => LocaleText::blank($request->input('country')),
            'medical_service_id' => $request->input('medical_service_id'),
            'message' => $request->input('message'),
            'consent' => true,
            'status' => 'new',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('medical.index')->with('sent', __('ui.medical.stored'));
    }

    /**
     * @param  class-string  $class
     * @param  list<string>  $with
     */
    private function cards(string $class, array $with = []): \Illuminate\Support\Collection
    {
        return $class::query()->published()->with(array_merge(['translations'], $with))->orderBy('position')->get()
            ->filter(fn ($record) => $record->publicTranslation() !== null)
            ->values();
    }

    /**
     * @param  class-string  $class
     */
    private function current(string $class, string $label): \Illuminate\Support\Collection
    {
        $locale = app()->getLocale();

        return $class::query()->published()->with('translations')->orderBy('position')->get()
            ->map(fn ($record) => $record->translations->firstWhere('locale', $locale))
            ->filter(fn ($row) => $row && filled($row->{$label}))
            ->values();
    }

    /**
     * @return list<array{label: string, url: string}>
     */
    private function crumbs(string $label, string $url): array
    {
        return [
            ['label' => __('ui.nav.home'), 'url' => route('home')],
            ['label' => __('ui.medical.title'), 'url' => route('medical.index')],
            ['label' => $label, 'url' => $url],
        ];
    }

    private function seo(ContentPresenter $presenter, object $translation, string $label, string $url, ?string $image = null): SeoData
    {
        return new SeoData(
            title: $presenter->documentTitle($translation->meta_title ?? null, (string) $translation->{$label}),
            description: (string) ($translation->meta_description ?: $translation->summary ?: ''),
            canonical: $presenter->canonical($translation->canonical_url ?? null, $url),
            image: $image,
        );
    }
}
