<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MedicalInquiryUpdateRequest;
use App\Models\MedicalInquiry;
use App\Support\LocaleText;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MedicalInquiryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', MedicalInquiry::class);

        return view('admin.medical.inquiries.index', [
            'records' => \App\Support\AdminList::paginate(MedicalInquiry::query()->with('service.translations'), request()),
        ]);
    }

    public function show(MedicalInquiry $inquiry): View
    {
        $this->authorize('view', $inquiry);

        return view('admin.medical.inquiries.show', [
            'inquiry' => $inquiry->load('service.translations'),
        ]);
    }

    public function update(MedicalInquiryUpdateRequest $request, MedicalInquiry $inquiry): RedirectResponse
    {
        $this->authorize('update', $inquiry);
        $inquiry->update([
            'status' => $request->input('status'),
            'admin_note' => LocaleText::blank($request->input('admin_note')),
        ]);

        return redirect()->route('admin.medical.inquiries.show', $inquiry)->with('status', __('admin.saved'));
    }

    public function destroy(MedicalInquiry $inquiry): RedirectResponse
    {
        $this->authorize('delete', $inquiry);
        $this->requireDeleteConfirmation();
        $inquiry->delete();

        return redirect()->route('admin.medical.inquiries.index')->with('status', __('admin.saved'));
    }
}
