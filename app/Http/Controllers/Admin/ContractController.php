<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booth;
use App\Models\BoothContract;
use App\Services\ContractService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * مدیریت قراردادهای غرفه‌ها: صدور (همراه با تعیین سهم)، مشاهده، چاپ/PDF و ابطال.
 */
class ContractController extends Controller
{
    public function __construct(protected ContractService $contracts) {}

    public function index(Request $request)
    {
        $status = in_array($request->query('status'), ['awaiting_signature', 'signed', 'superseded', 'void'], true)
            ? $request->query('status') : null;

        $contracts = BoothContract::with('booth')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $counts = BoothContract::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        return view('admin.contracts.index', compact('contracts', 'status', 'counts'));
    }

    public function show(BoothContract $contract)
    {
        $contract->load('booth.owner', 'issuer');

        return view('admin.contracts.show', compact('contract'));
    }

    public function print(Request $request, BoothContract $contract)
    {
        return view('contracts.print', [
            'contract' => $contract,
            'autoPrint' => $request->boolean('pdf'),
        ]);
    }

    /** تعیین سهم غرفه + صدور قرارداد برای امضا در پنل غرفه‌دار */
    public function issue(Request $request, Booth $booth)
    {
        $data = $request->validate([
            'share_percent' => 'required|numeric|min:1|max:100',
            'settlement_cycle_days' => 'required|integer|min:1|max:90',
            'term_months' => 'required|integer|min:1|max:60',
            'special_terms' => 'nullable|string|max:3000',
        ]);

        $contract = $this->contracts->issue(
            $booth,
            (float) $data['share_percent'],
            (int) $data['settlement_cycle_days'],
            (int) $data['term_months'],
            $data['special_terms'] ?? null,
            Auth::user(),
        );

        return redirect()->route('admin.booths.show', $booth)
            ->with('success', __('wallet.issued_ok', [
                'number' => $contract->contract_number,
                'share' => $contract->share_percent_label,
            ]));
    }

    public function void(Request $request, BoothContract $contract)
    {
        $data = $request->validate(['void_reason' => 'required|string|max:480']);

        $this->contracts->void($contract, $data['void_reason']);

        return back()->with('success', __('wallet.voided_ok'));
    }
}
