<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Inquiry::class);

        return view('admin.inquiries.index', [
            'inquiries' => Inquiry::query()->latest()->paginate(20),
        ]);
    }

    public function show(Inquiry $inquiry): View
    {
        $this->authorize('view', $inquiry);

        return view('admin.inquiries.show', [
            'inquiry' => $inquiry,
        ]);
    }
}
