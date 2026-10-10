<?php

namespace App\Services;

use App\Models\Booth;
use App\Models\BoothContract;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * صدور، امضا و ابطال قرارداد همکاری غرفه‌ها.
 *
 * اصل کار: لحظه‌ی صدور، متن قرارداد و مشخصات طرفین «اسنپ‌شات» می‌شن و یک هش
 * (SHA-256) از کل سند ذخیره می‌شه. موقع امضا دوباره هش محاسبه و مقایسه می‌شه؛
 * پس اگه محتوای ذخیره‌شده‌ی قرارداد بین صدور و امضا دست‌کاری شده باشه، امضا
 * پذیرفته نمی‌شه.
 */
class ContractService
{
    public const MAX_SIGNATURE_BYTES = 600000;

    public function __construct(protected SmsService $sms) {}

    /**
     * صدور قرارداد جدید برای غرفه. همزمان:
     *  - درصد کمیسیون غرفه (مکمل سهم) به‌روز می‌شه تا سفارش‌های جدید طبق همین محاسبه بشن
     *    (سفارش‌های قبلی روی آیتم‌هاشون قفل هستن و تغییر نمی‌کنن)،
     *  - قرارداد معتبر قبلی «جایگزین‌شده» می‌شه (سابقه‌اش می‌مونه)،
     *  - به غرفه‌دار پیامک اطلاع داده می‌شه.
     */
    public function issue(Booth $booth, float $share, int $cycleDays, int $termMonths, ?string $special, User $admin): BoothContract
    {
        $share = round($share, 2);
        $commission = round(100 - $share, 2);
        $special = ($special !== null && trim($special) !== '') ? trim($special) : null;

        $contract = DB::transaction(function () use ($booth, $share, $commission, $cycleDays, $termMonths, $special, $admin) {
            BoothContract::where('booth_id', $booth->id)
                ->whereIn('status', [BoothContract::AWAITING, BoothContract::SIGNED])
                ->update(['status' => BoothContract::SUPERSEDED]);

            $booth->update(['commission_percent' => $commission]);

            $number = $this->newContractNumber();
            $terms = ContractTerms::build($share, $commission, $cycleDays, $termMonths, $special);
            $parties = $this->partiesFor($booth->fresh(['owner']));

            return BoothContract::create([
                'booth_id' => $booth->id,
                'contract_number' => $number,
                'status' => BoothContract::AWAITING,
                'share_percent' => $share,
                'commission_percent' => $commission,
                'settlement_cycle_days' => $cycleDays,
                'term_months' => $termMonths,
                'special_terms' => $special,
                'terms' => $terms,
                'parties' => $parties,
                'document_hash' => $this->hashOf($number, $share, $commission, $cycleDays, $termMonths, $special, $terms, $parties),
                'issued_by' => $admin->id,
                'issued_at' => now(),
            ]);
        });

        $this->notifyIssued($booth, $contract);

        return $contract;
    }

    /**
     * ثبت امضای غرفه‌دار. همه‌چیز (تصویر امضا، نام، زمان، IP، دستگاه، پذیرش شرایط)
     * همراه با هش سند ذخیره می‌شه تا بعداً قابل اثبات باشه.
     */
    public function sign(BoothContract $contract, string $signerName, string $signatureDataUri, Request $request): BoothContract
    {
        $png = $this->decodeSignature($signatureDataUri);

        return DB::transaction(function () use ($contract, $signerName, $png, $request) {
            /** @var BoothContract $fresh */
            $fresh = BoothContract::lockForUpdate()->findOrFail($contract->id);

            if (! $fresh->isAwaitingSignature()) {
                throw ValidationException::withMessages(['signature' => __('wallet.err_not_signable')]);
            }

            if (! $this->verifyIntegrity($fresh)) {
                throw ValidationException::withMessages(['signature' => __('wallet.err_integrity')]);
            }

            $path = 'contracts/signatures/'.$fresh->contract_number.'.png';
            Storage::disk('local')->put($path, $png);

            $fresh->update([
                'status' => BoothContract::SIGNED,
                'signer_name' => Str::limit(trim($signerName), 120, ''),
                'signature_path' => $path,
                'accepted_terms' => true,
                'signed_at' => now(),
                'signer_ip' => $request->ip(),
                'signer_user_agent' => Str::limit((string) $request->userAgent(), 480, ''),
            ]);

            return $fresh;
        });
    }

    /** ابطال قرارداد توسط ادمین (با دلیل). سابقه حذف نمی‌شه. */
    public function void(BoothContract $contract, string $reason): BoothContract
    {
        $contract->update([
            'status' => BoothContract::VOID,
            'voided_at' => now(),
            'void_reason' => Str::limit($reason, 480, ''),
        ]);

        return $contract;
    }

    /** آیا محتوای ذخیره‌شده‌ی قرارداد هنوز با هش زمان صدور یکیه؟ */
    public function verifyIntegrity(BoothContract $contract): bool
    {
        $expected = $this->hashOf(
            $contract->contract_number,
            (float) $contract->share_percent,
            (float) $contract->commission_percent,
            (int) $contract->settlement_cycle_days,
            (int) $contract->term_months,
            $contract->special_terms,
            $contract->terms,
            $contract->parties,
        );

        return hash_equals($contract->document_hash, $expected);
    }

    /** مشخصات دو طرف قرارداد (فارسی، مستقل از زبان فعلی صفحه — سند حقوقی همیشه یک متن ثابت داره) */
    public function partiesFor(Booth $booth): array
    {
        $owner = $booth->owner;
        $name = $booth->name['fa'] ?? (is_array($booth->name) ? (reset($booth->name) ?: '') : (string) $booth->name);

        return [
            'company' => [
                'name' => 'هلدینگ مهرگان (هاچ لند)',
                'website' => 'hachland.com',
                'phone' => '09111775006',
                'email' => __('site.footer.email_address', [], 'fa'),
            ],
            'booth' => [
                'booth_name' => $name,
                'owner_name' => $owner?->name ?? '',
                'national_id' => $booth->national_id,
                'phone' => $booth->phone ?: $owner?->phone,
                'location' => trim(($booth->city ?? '').($booth->city && $booth->province ? '، ' : '').($booth->province ?? '')),
                'bank_account_holder' => $booth->bank_account_holder,
                'bank_name' => $booth->bank_name,
                'bank_iban' => $booth->bank_iban ? 'IR'.$booth->bank_iban : null,
            ],
        ];
    }

    protected function hashOf(string $number, float $share, float $commission, int $cycle, int $term, ?string $special, array $terms, array $parties): string
    {
        return hash('sha256', json_encode([
            'n' => $number,
            's' => number_format($share, 2, '.', ''),
            'c' => number_format($commission, 2, '.', ''),
            'cy' => $cycle,
            't' => $term,
            'sp' => $special,
            'terms' => $terms,
            'parties' => $parties,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    protected function newContractNumber(): string
    {
        do {
            $number = 'HLC-'.now()->format('ymd').'-'.strtoupper(Str::random(5));
        } while (BoothContract::where('contract_number', $number)->exists());

        return $number;
    }

    /**
     * تصویر امضا رو از data-URI به PNG معتبر تبدیل می‌کنه و مطمئن می‌شه واقعاً
     * چیزی روش کشیده شده (نه یه بوم خالی) و حجمش معقوله.
     */
    protected function decodeSignature(string $dataUri): string
    {
        $fail = fn (string $key) => ValidationException::withMessages(['signature' => __('wallet.'.$key)]);

        if (! preg_match('#^data:image/png;base64,([A-Za-z0-9+/=]+)$#', $dataUri, $m)) {
            throw $fail('err_signature_required');
        }

        $binary = base64_decode($m[1], true);

        if ($binary === false || strlen($binary) > self::MAX_SIGNATURE_BYTES) {
            throw $fail('err_signature_invalid');
        }

        $info = @getimagesizefromstring($binary);

        if (! $info || $info[2] !== IMAGETYPE_PNG || $info[0] < 60 || $info[1] < 30 || $info[0] > 3000 || $info[1] > 2000) {
            throw $fail('err_signature_invalid');
        }

        if (function_exists('imagecreatefromstring')) {
            $img = @imagecreatefromstring($binary);
            if ($img !== false) {
                $inked = 0;
                $w = imagesx($img);
                $h = imagesy($img);
                $step = max(1, (int) floor(min($w, $h) / 60));

                for ($y = 0; $y < $h && $inked < 40; $y += $step) {
                    for ($x = 0; $x < $w && $inked < 40; $x += $step) {
                        if ((((imagecolorat($img, $x, $y)) >> 24) & 0x7F) < 100) {
                            $inked++;
                        }
                    }
                }
                imagedestroy($img);

                if ($inked < 40) {
                    throw $fail('err_signature_blank');
                }
            }
        }

        return $binary;
    }

    protected function notifyIssued(Booth $booth, BoothContract $contract): void
    {
        $phone = $booth->owner?->phone;

        if (! $phone) {
            return;
        }

        try {
            $this->sms->sendRaw($phone, 'قرارداد همکاری غرفه «'.($booth->name['fa'] ?? '').'» با سهم '.BoothContract::formatPercent($contract->share_percent).'٪ در پنل شما آماده امضاست. هاچ لند');
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
