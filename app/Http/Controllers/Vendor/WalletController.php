<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * کیف پول غرفه‌دار: مانده، سهم از فروش و صورت‌ریز کامل اسناد حسابداری.
 */
class WalletController extends Controller
{
    public function index(Request $request, WalletService $wallet)
    {
        $booth = Auth::user()->booth;
        $summary = $wallet->summary($booth);

        $type = in_array($request->query('type'), ['sale', 'reversal', 'adjustment', 'payout'], true)
            ? $request->query('type')
            : null;

        $transactions = $booth->walletTransactions()
            ->with('order')
            ->when($type, fn ($q) => $q->where('type', $type))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // «مانده بعد از هر سند» فقط وقتی معنی داره که فیلتر نوع نداشته باشیم
        $running = null;
        if (! $type && $transactions->count()) {
            $newerSum = (int) WalletTransaction::where('booth_id', $booth->id)
                ->where('id', '>', $transactions->first()->id)->sum('amount');
            $running = $summary['balance'] - $newerSum;
        }

        $contract = $booth->currentContract();

        return view('vendor.wallet.index', compact('booth', 'summary', 'transactions', 'type', 'running', 'contract'));
    }
}
