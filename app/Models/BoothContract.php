<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * قرارداد همکاری غرفه با هاچ لند — نگاه کنید به ContractService/ContractTerms.
 */
class BoothContract extends Model
{
    public const AWAITING = 'awaiting_signature';
    public const SIGNED = 'signed';
    public const SUPERSEDED = 'superseded';
    public const VOID = 'void';

    protected $fillable = [
        'booth_id', 'contract_number', 'status',
        'share_percent', 'commission_percent', 'settlement_cycle_days', 'term_months', 'special_terms',
        'terms', 'parties', 'document_hash', 'issued_by', 'issued_at',
        'signer_name', 'signature_path', 'accepted_terms', 'signed_at', 'signer_ip', 'signer_user_agent',
        'voided_at', 'void_reason',
    ];

    protected function casts(): array
    {
        return [
            'share_percent' => 'decimal:2',
            'commission_percent' => 'decimal:2',
            'terms' => 'array',
            'parties' => 'array',
            'accepted_terms' => 'boolean',
            'issued_at' => 'datetime',
            'signed_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    public function booth(): BelongsTo
    {
        return $this->belongsTo(Booth::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function isSigned(): bool
    {
        return $this->status === self::SIGNED;
    }

    public function isAwaitingSignature(): bool
    {
        return $this->status === self::AWAITING;
    }

    /** قراردادی که هنوز معتبر/فعاله (امضاشده یا منتظر امضا) */
    public function scopeCurrent($query)
    {
        return $query->whereIn('status', [self::AWAITING, self::SIGNED]);
    }

    public function scopeAwaitingSignature($query)
    {
        return $query->where('status', self::AWAITING);
    }

    public function getStatusLabelAttribute(): string
    {
        return __('wallet.contract_status_'.$this->status);
    }

    /** درصد بدون صفر اضافه‌ی اعشار: 85.00 → 85 و 87.50 → 87٫5 */
    public static function formatPercent(float|string $value): string
    {
        $v = rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');

        return app()->getLocale() === 'fa' ? str_replace('.', '٫', $v) : $v;
    }

    public function getSharePercentLabelAttribute(): string
    {
        return self::formatPercent($this->share_percent);
    }

    public function getCommissionPercentLabelAttribute(): string
    {
        return self::formatPercent($this->commission_percent);
    }

    /** تاریخ پایان اعتبار (بر اساس تاریخ امضا؛ اگه هنوز امضا نشده null) */
    public function getExpiresAtAttribute(): ?\Carbon\CarbonInterface
    {
        return $this->signed_at?->copy()->addMonths((int) $this->term_months);
    }

    /** کد رهگیری/تطبیق کوتاه از روی هش سند — برای اثبات یکسان‌بودن نسخه‌ی چاپی با نسخه‌ی ذخیره‌شده */
    public function getVerificationCodeAttribute(): string
    {
        return strtoupper(implode('-', str_split(substr($this->document_hash, 0, 12), 4)));
    }

    /** تصویر امضا به‌صورت data-URI تا هم تو صفحه و هم تو نسخه‌ی چاپی بدون مسیر عمومی نمایش داده بشه */
    public function getSignatureDataUriAttribute(): ?string
    {
        if (! $this->signature_path || ! Storage::disk('local')->exists($this->signature_path)) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode(Storage::disk('local')->get($this->signature_path));
    }
}
