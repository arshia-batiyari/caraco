<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Inquiry;
use App\Models\Page;
use App\Models\Project;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'divisions' => Division::query()->count(),
            'publishedDivisions' => Division::query()->where('is_published', true)->count(),
            'projects' => Project::query()->count(),
            'publishedProjects' => Project::query()->where('is_published', true)->count(),
            'pages' => Page::query()->count(),
            'publishedPages' => Page::query()->where('is_published', true)->count(),
            'inquiries' => Inquiry::query()->count(),
            'recent' => Inquiry::query()->latest()->limit(5)->get(),
        ]);
    }
}
