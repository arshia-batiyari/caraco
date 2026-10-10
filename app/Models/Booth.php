<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booth extends Model
{
    protected $fillable = [
        'user_id', 'name', 'slug', 'description', 'logo', 'banner',
        'verification_document', 'national_id',
        'province', 'city', 'phone', 'status', 'rejection_reason',
        'online_payment_enabled', 'commission_percent', 'approved_at',
        'bank_account_holder', 'bank_iban', 'bank_name',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'online_payment_enabled' => 'boolean',
            'commission_percent' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(Settlement::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(BoothContract::class)->latest('id');
    }

    /** آخرین قرارداد معتبر (امضاشده یا منتظر امضا) */
    public function currentContract(): ?BoothContract
    {
        return $this->contracts()->current()->first();
    }

    /**
     * سهم غرفه‌دار از فروش (٪) — مکمل کمیسیون هاچ لند.
     * ستون اصلی همون commission_percent می‌مونه (برای اینکه محاسبه‌ی سفارش‌ها،
     * تسویه و گزارش‌های قبلی دست‌نخورده بمونن)؛ سهم فقط روی همون بنا شده.
     */
    public function getSharePercentAttribute(): float
    {
        return round(100 - (float) $this->commission_percent, 2);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /** غرفه‌های تأییدشده‌ی دسته مصالح ساختمانی و زیردسته‌هایش. */
    public function scopeMaterialSuppliers($query)
    {
        return $query->approved()->whereHas('categories', function ($q) {
            $q->where('slug', 'like', 'building-%');
        });
    }

    public function getLocalizedNameAttribute(): string
    {
        return $this->name[app()->getLocale()] ?? $this->name['fa'] ?? '';
    }

    /**
     * بنر غرفه. اگر غرفه‌دار بنری آپلود نکرده باشه، یک بنر جایگزین نمایشی
     * بر اساس دسته‌بندی اصلی غرفه نمایش داده می‌شه تا صفحه خالی نمونه.
     */
    public function getBannerUrlAttribute(): string
    {
        if ($this->banner) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($this->banner);
        }

        $rawSlug = $this->categories->first()?->slug ?? 'default';
        $slug = match (true) {
            str_starts_with($rawSlug, 'real-estate') => 'real-estate',
            default => $rawSlug,
        };

        return asset('images/placeholders/banners/'.(
            file_exists(public_path('images/placeholders/banners/'.$slug.'.webp')) ? $slug : 'default'
        ).'.webp');
    }

    /**
     * لوگوی غرفه. اگر غرفه‌دار موقع ثبت‌نام لوگو آپلود نکرده باشه null
     * برمی‌گرده (برخلاف بنر) — چون تو این حالت طراحی صفحه به‌جای لوگو یک
     * آواتار حرفی نمایش می‌ده که خودش کافیه و نیازی به عکس جایگزین نیست.
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->logo) : null;
    }

    /**
     * مجموع مبلغی که هنوز به این غرفه واریز نشده
     * (آیتم‌های سفارش پرداخت‌شده که در هیچ settlement‌ای نیستن)
     */
    public function getUnsettledPayoutAttribute(): int
    {
        return (int) $this->orderItems()
            ->whereNull('settlement_id')
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'paid'))
            ->sum('booth_payout_amount');
    }
}