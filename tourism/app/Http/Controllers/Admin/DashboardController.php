<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attraction;
use App\Models\Destination;
use App\Models\FeedbackReview;
use App\Models\Hotel;
use App\Models\Inquiry;
use App\Models\MedicalInquiry;
use App\Models\MedicalService;
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
            'publishedHotels' => Hotel::query()->where('is_published', true)->count(),
            'destinations' => Destination::query()->count(),
            'publishedDestinations' => Destination::query()->where('is_published', true)->count(),
            'attractions' => Attraction::query()->count(),
            'publishedAttractions' => Attraction::query()->where('is_published', true)->count(),
            'medicalServices' => MedicalService::query()->count(),
            'publishedMedical' => MedicalService::query()->where('is_published', true)->count(),
            'inquiries' => Inquiry::query()->count(),
            'openInquiries' => Inquiry::query()->where('status', '!=', 'closed')->count(),
            'medicalInquiries' => MedicalInquiry::query()->count(),
            'openMedical' => MedicalInquiry::query()->where('status', '!=', 'closed')->count(),
            'pendingReviews' => FeedbackReview::query()->where('status', 'pending')->count(),
            'recent' => Inquiry::query()->latest()->limit(5)->get(),
            'recentMedical' => MedicalInquiry::query()->latest()->limit(5)->get(),
        ]);
    }
}
