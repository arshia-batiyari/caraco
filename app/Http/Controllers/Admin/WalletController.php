<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booth;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * حسابداری کیف پول غرفه‌ها برای ادمین: نمای کلی، صورت‌ریز هر غرفه و سند اصلاحی دستی.
 */
class WalletController extends Controller
{
    public function index(WalletService $wallet)
    {
        $booths = Booth::with('owner')->where('status', 'approved')->orderBy('id')->paginate(20);

        $rows = WalletTransaction::whereIn('booth_id', $booths->pluck('id'))
            ->select('booth_id')
            ->selectRaw('SUM(amount) as balance')
            ->selectRaw("SUM(CASE WHEN type IN ('sale','reversal') THEN gross_amount ELSE 0 END) as gross")
            ->selectRaw("SUM(CASE WHEN type IN ('sale','reversal') THEN commission_amount ELSE 0 END) as commission")
            ->selectRaw("SUM(CASE WHEN type = 'payout' THEN -amount ELSE 0 END) as paid_out")
            ->groupBy('booth_id')
            ->get()
            ->keyBy('booth_id');

        // جمع کل پلتفرم (همه‌ی غرفه‌ها، نه فقط همین صفحه)
        $totals = [
            'gross' => (int) WalletTransaction::whereIn('type', ['sale', 'reversal'])->sum('gross_amount'),
            'commission' => (int) WalletTransaction::whereIn('type', ['sale', 'reversal'])->sum('commission_amount'),
            'owed' => (int) WalletTransaction::sum('amount'),
            'paid_out' => -1 * (int) WalletTransaction::where('type', 'payout')->sum('amount'),
        ];

        return view('admin.wallets.index', compact('booths', 'rows', 'totals'));
    }

    public function show(Request $request, Booth $booth, WalletService $wallet)
    {
        $booth->load('owner');
        $summary = $wallet->summary($booth);

        $type = in_array($request->query('type'), ['sale', 'reversal', 'adjustment', 'payout'], true)
            ? $request->query('type') : null;

        $transactions = $booth->walletTransactions()
            ->with('order', 'creator')
            ->when($type, fn ($q) => $q->where('type', $type))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $running = null;
        if (! $type && $transactions->count()) {
            $newerSum = (int) WalletTransaction::where('booth_id', $booth->id)
                ->where('id', '>', $transactions->first()->id)->sum('amount');
            $running = $summary['balance'] - $newerSum;
        }

        $contract = $booth->currentContract();

        return view('admin.wallets.show', compact('booth', 'summary', 'transactions', 'type', 'running', 'contract'));
    }

    /** سند اصلاحی دستی: بستانکار (افزایش) یا بدهکار (کاهش) با شرح اجباری */
    public function adjust(Request $request, Booth $booth, WalletService $wallet)
    {
        $data = $request->validate([
            'direction' => 'required|in:credit,debit',
            'amount' => 'required|integer|min:1|max:1000000000',
            'description' => 'required|string|min:3|max:500',
        ]);

        $signed = $data['direction'] === 'credit' ? (int) $data['amount'] : -1 * (int) $data['amount'];

        DB::transaction(fn () => $wallet->adjust($booth, $signed, $data['description'], Auth::user()));

        return back()->with('success', __('wallet.adjust_ok'));
    }
}
