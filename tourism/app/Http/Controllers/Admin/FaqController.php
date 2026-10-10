<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqRequest;
use App\Models\Faq;
use App\Support\LocaleText;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Faq::class);

        return view('admin.faqs.index', [
            'records' => Faq::query()->whereNull('tour_id')->with('translations')->orderBy('position')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Faq::class);

        return view('admin.faqs.form', ['record' => new Faq(['position' => 0])]);
    }

    public function store(FaqRequest $request): RedirectResponse
    {
        $this->authorize('create', Faq::class);
        $record = Faq::query()->create([
            'tour_id' => null,
            'is_published' => $request->boolean('is_published'),
            'position' => $request->integer('position'),
        ]);
        LocaleText::sync($record, $request->input('translations', []), ['question', 'answer'], 'question');

        return redirect()->route('admin.faqs.edit', $record)->with('status', __('admin.saved'));
    }

    public function edit(Faq $faq): View
    {
        abort_if($faq->tour_id !== null, 404);
        $this->authorize('update', $faq);

        return view('admin.faqs.form', ['record' => $faq->load('translations')]);
    }

    public function update(FaqRequest $request, Faq $faq): RedirectResponse
    {
        abort_if($faq->tour_id !== null, 404);
        $this->authorize('update', $faq);
        $faq->update([
            'is_published' => $request->boolean('is_published'),
            'position' => $request->integer('position'),
        ]);
        LocaleText::sync($faq, $request->input('translations', []), ['question', 'answer'], 'question');

        return redirect()->route('admin.faqs.edit', $faq)->with('status', __('admin.saved'));
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        abort_if($faq->tour_id !== null, 404);
        $this->authorize('delete', $faq);
        $faq->delete();

        return redirect()->route('admin.faqs.index')->with('status', __('admin.saved'));
    }
}
