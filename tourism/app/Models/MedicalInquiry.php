<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalInquiry extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'locale',
        'preferred_language',
        'country',
        'medical_service_id',
        'message',
        'consent',
        'status',
        'admin_note',
        'ip_address',
    ];

    protected function casts(): array
    {
        return ['consent' => 'boolean'];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(MedicalService::class, 'medical_service_id');
    }
}
