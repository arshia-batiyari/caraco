<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\BoothContract;
use App\Services\ContractService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * قرارداد همکاری از دید غرفه‌دار: مشاهده، امضای دیجیتال و دریافت PDF.
 */
class ContractController extends Controller
{
    public function __construct(protected ContractService $contracts) {}

    /** آخرین قرارداد معتبر رو نشون می‌ده؛ اگه هنوز قراردادی صادر نشده، صفحه‌ی توضیح */
    public function index()
    {
        $booth = Auth::user()->booth;
        $current = $booth->currentContract();

        if ($current) {
            return redirect()->route('vendor.contract.show', $current);
        }

        $history = $booth->contracts()->get();

        return view('vendor.contract.empty', compact('booth', 'history'));
    }

    public function show(BoothContract $contract)
    {
        $this->authorizeOwner($contract);

        $history = Auth::user()->booth->contracts()->where('id', '!=', $contract->id)->get();

        return view('vendor.contract.show', compact('contract', 'history'));
    }

    public function sign(Request $request, BoothContract $contract)
    {
        $this->authorizeOwner($contract);

        $data = $request->validate([
            'signer_name' => 'required|string|min:3|max:120',
            'accepted' => 'accepted',
            'signature' => 'required|string',
        ], [
            'signer_name.required' => __('wallet.err_name_required'),
            'signer_name.min' => __('wallet.err_name_required'),
            'accepted.accepted' => __('wallet.err_accept_required'),
            'signature.required' => __('wallet.err_signature_required'),
        ]);

        $this->contracts->sign($contract, $data['signer_name'], $data['signature'], $request);

        return redirect()->route('vendor.contract.show', $contract)->with('success', __('wallet.signed_ok'));
    }

    /** نسخه‌ی چاپی A4؛ با ?pdf=1 خودکار پنجره‌ی «ذخیره به‌صورت PDF» باز می‌شه */
    public function print(Request $request, BoothContract $contract)
    {
        $this->authorizeOwner($contract);

        return view('contracts.print', [
            'contract' => $contract,
            'autoPrint' => $request->boolean('pdf'),
        ]);
    }

    protected function authorizeOwner(BoothContract $contract): void
    {
        abort_unless($contract->booth_id === Auth::user()->booth?->id, 404);
    }
}
