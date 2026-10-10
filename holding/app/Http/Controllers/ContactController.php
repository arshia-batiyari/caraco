<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInquiryRequest;
use App\Models\Inquiry;
use App\Support\ContentPresenter;
use App\Support\SeoData;
use App\Support\SiteContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(ContentPresenter $presenter, SiteContext $site): View
    {
        $canonical = route('contact');

        return $presenter->render('pages.contact', [
            'contact' => $site->contact(),
            'email' => $site->email(),
            'phone' => $site->phone(),
            'schema' => $presenter->breadcrumb(__('ui.contact_page.headline'), $canonical),
        ], new SeoData(
            title: $presenter->documentTitle(null, __('ui.contact_page.headline')),
            description: __('ui.contact_page.lede'),
            canonical: $canonical,
        ));
    }

    public function store(StoreInquiryRequest $request): RedirectResponse
    {
        if (! $request->honeypotFilled()) {
            Inquiry::query()->create([
                'name' => $request->string('name')->trim()->toString(),
                'email' => $request->string('email')->trim()->lower()->toString(),
                'message' => $request->string('message')->trim()->toString(),
                'locale' => app()->getLocale(),
                'ip_address' => $request->ip(),
            ]);
        }

        return redirect()->route('contact')->with('sent', true);
    }
}
