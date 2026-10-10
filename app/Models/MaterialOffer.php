<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialOffer extends Model
{
    protected $fillable = [
        'material_request_id', 'user_id', 'booth_id',
        'name', 'phone', 'quoted_amount', 'lead_days', 'message',
        'status', 'admin_note',
    ];

    public function materialRequest(): BelongsTo
    {
        return $this->belongsTo(MaterialRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function booth(): BelongsTo
    {
        return $this->belongsTo(Booth::class);
    }
}
