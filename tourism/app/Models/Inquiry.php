<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inquiry extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'locale',
        'preferred_language',
        'tour_id',
        'destination_id',
        'destination_text',
        'travel_start',
        'travel_end',
        'travelers',
        'message',
        'consent',
        'status',
        'assigned_to',
        'admin_note',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'consent' => 'boolean',
            'travel_start' => 'date',
            'travel_end' => 'date',
        ];
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
