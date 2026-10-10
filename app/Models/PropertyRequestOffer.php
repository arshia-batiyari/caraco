<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * وقتی مالک/سازنده‌ای می‌بینه آگهی درخواستِ یکی با چیزی که داره جور در میاد،
 * از همین‌جا اعلام آمادگی می‌کنه. فقط ادمین این رکوردها رو می‌بینه.
 */
class PropertyRequestOffer extends Model
{
    protected $fillable = [
        'property_request_id', 'user_id', 'name', 'phone', 'message', 'status', 'admin_note',
    ];

    public function propertyRequest(): BelongsTo
    {
        return $this->belongsTo(PropertyRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
