<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MedicalStepRequest;
use App\Models\MedicalStep;
use App\Support\LocaleText;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MedicalStepController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', MedicalStep::class);

        return view('admin.medical.steps.index', [
            'records' => \App\Support\AdminList::paginate(MedicalStep::query()->with('translations'), request(), 'title'),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', MedicalStep::class);

        return view('admin.medical.steps.form', [
            'record' => new MedicalStep(['position' => 0, 'is_published' => false]),
        ]);
    }

    public function store(MedicalStepRequest $request): RedirectResponse
    {
        $this->authorize('create', MedicalStep::class);
        $record = MedicalStep::query()->create([
            'is_published' => $request->boolean('is_published'),
            'position' => $request->integer('position'),
        ]);
        LocaleText::sync($record, $request->input('translations', []), ['title', 'body'], 'title');

        return redirect()->route('admin.medical.steps.edit', $record)->with('status', __('admin.saved'));
    }

    public function edit(MedicalStep $step): View
    {
        $this->authorize('update', $step);

        return view('admin.medical.steps.form', ['record' => $step->load('translations')]);
    }

    public function update(MedicalStepRequest $request, MedicalStep $step): RedirectResponse
    {
        $this->authorize('update', $step);
        $step->update([
            'is_published' => $request->boolean('is_published'),
            'position' => $request->integer('position'),
        ]);
        LocaleText::sync($step, $request->input('translations', []), ['title', 'body'], 'title');

        return redirect()->route('admin.medical.steps.edit', $step)->with('status', __('admin.saved'));
    }

    public function destroy(MedicalStep $step): RedirectResponse
    {
        $this->authorize('delete', $step);
        $this->requireDeleteConfirmation();
        $step->delete();

        return redirect()->route('admin.medical.steps.index')->with('status', __('admin.saved'));
    }
}
