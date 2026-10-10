<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Inquiry;
use App\Models\Tour;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'tours' => Tour::query()->count(),
            'publishedTours' => Tour::query()->where('is_published', true)->count(),
            'hotels' => Hotel::query()->count(),
            'inquiries' => Inquiry::query()->count(),
            'openInquiries' => Inquiry::query()->where('status', '!=', 'closed')->count(),
            'recent' => Inquiry::query()->latest()->limit(6)->get(),
        ]);
    }
}
