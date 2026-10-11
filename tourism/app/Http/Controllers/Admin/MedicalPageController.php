<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MedicalPageRequest;
use App\Models\MedicalPage;
use App\Support\LocaleText;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MedicalPageController extends Controller
{
    public function home(): View
    {
        $this->authorize('viewAny', MedicalPage::class);

        return view('admin.medical.home');
    }

    public function edit(): View
    {
        $this->authorize('viewAny', MedicalPage::class);
        $record = MedicalPage::query()->with('translations')->first() ?? new MedicalPage;

        return view('admin.medical.page', ['record' => $record]);
    }

    public function update(MedicalPageRequest $request): RedirectResponse
    {
        $record = MedicalPage::query()->first();
        if ($record) {
            $this->authorize('update', $record);
        } else {
            $this->authorize('create', MedicalPage::class);
            $record = MedicalPage::query()->create();
        }
        LocaleText::sync($record, $request->input('translations', []), [
            'title', 'summary', 'body', 'travel_assistance', 'stay_assistance', 'contact_note',
            'meta_title', 'meta_description', 'canonical_url',
        ], 'title');

        return redirect()->route('admin.medical.page')->with('status', __('admin.saved'));
    }
}
