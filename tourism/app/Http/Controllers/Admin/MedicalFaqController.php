<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MedicalFaqRequest;
use App\Models\MedicalFaq;
use App\Support\LocaleText;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MedicalFaqController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', MedicalFaq::class);

        return view('admin.medical.faqs.index', [
            'records' => \App\Support\AdminList::paginate(MedicalFaq::query()->with('translations'), request(), 'question'),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', MedicalFaq::class);

        return view('admin.medical.faqs.form', [
            'record' => new MedicalFaq(['position' => 0, 'is_published' => false]),
        ]);
    }

    public function store(MedicalFaqRequest $request): RedirectResponse
    {
        $this->authorize('create', MedicalFaq::class);
        $record = MedicalFaq::query()->create([
            'is_published' => $request->boolean('is_published'),
            'position' => $request->integer('position'),
        ]);
        LocaleText::sync($record, $request->input('translations', []), ['question', 'answer'], 'question');

        return redirect()->route('admin.medical.faqs.edit', $record)->with('status', __('admin.saved'));
    }

    public function edit(MedicalFaq $faq): View
    {
        $this->authorize('update', $faq);

        return view('admin.medical.faqs.form', ['record' => $faq->load('translations')]);
    }

    public function update(MedicalFaqRequest $request, MedicalFaq $faq): RedirectResponse
    {
        $this->authorize('update', $faq);
        $faq->update([
            'is_published' => $request->boolean('is_published'),
            'position' => $request->integer('position'),
        ]);
        LocaleText::sync($faq, $request->input('translations', []), ['question', 'answer'], 'question');

        return redirect()->route('admin.medical.faqs.edit', $faq)->with('status', __('admin.saved'));
    }

    public function destroy(MedicalFaq $faq): RedirectResponse
    {
        $this->authorize('delete', $faq);
        $this->requireDeleteConfirmation();
        $faq->delete();

        return redirect()->route('admin.medical.faqs.index')->with('status', __('admin.saved'));
    }
}
