<?php

namespace App\Http\Controllers\Landowner;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $properties = Auth::user()->properties()->latest()->get();

        $stats = [
            'total' => $properties->count(),
            'pending' => $properties->where('status', 'pending')->count(),
            'approved' => $properties->where('status', 'approved')->count(),
            'rejected' => $properties->where('status', 'rejected')->count(),
        ];

        return view('landowner.dashboard', compact('properties', 'stats'));
    }
}
