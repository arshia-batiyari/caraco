<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SettlementController extends Controller
{
    /** تاریخچه تسویه‌حساب‌ها + مانده در انتظار واریز */
    public function index()
    {
        $booth = Auth::user()->booth;

        $settlements = $booth->settlements()->latest('period_end')->paginate(15);
        $unsettledAmount = $booth->unsettled_payout;

        return view('vendor.settlements.index', compact('settlements', 'unsettledAmount'));
    }
}
