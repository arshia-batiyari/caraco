<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * درخواست ارتباط یک بازدیدکننده با مالک ملک/زمین (برای خرید یا مشارکت).
 * این رکورد فقط برای ادمین قابل مشاهده است — هیچ رابطه یا route‌ای برای
 * مالک ملک به این مدل تعریف نشده تا اطلاعات درخواست‌کننده هرگز به او نشتی نکند.
 */
class PropertyInquiry extends Model
{
    protected $fillable = [
        'property_id', 'user_id', 'name', 'phone', 'inquiry_type',
        'message', 'status', 'admin_note',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function getLocalizedInquiryTypeAttribute(): string
    {
        return __('site.properties.inquiry_types.'.$this->inquiry_type);
    }
}
