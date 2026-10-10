<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InquiryUpdateRequest;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Inquiry::class);

        return view('admin.inquiries.index', [
            'records' => Inquiry::query()->with('assignee')->latest()->paginate(20),
        ]);
    }

    public function show(Inquiry $inquiry): View
    {
        $this->authorize('view', $inquiry);

        return view('admin.inquiries.show', [
            'inquiry' => $inquiry->load('tour.translations', 'destination.translations', 'assignee'),
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function update(InquiryUpdateRequest $request, Inquiry $inquiry): RedirectResponse
    {
        $this->authorize('update', $inquiry);
        $inquiry->update([
            'status' => $request->input('status'),
            'assigned_to' => $request->input('assigned_to'),
            'admin_note' => $request->input('admin_note'),
        ]);

        return redirect()->route('admin.inquiries.show', $inquiry)->with('status', __('admin.saved'));
    }

    public function destroy(Inquiry $inquiry): RedirectResponse
    {
        $this->authorize('delete', $inquiry);
        $inquiry->delete();

        return redirect()->route('admin.inquiries.index')->with('status', __('admin.saved'));
    }
}
