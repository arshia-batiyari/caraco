<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DivisionTranslation extends Model
{
    protected $fillable = [
        'division_id',
        'locale',
        'title',
        'slug',
        'introduction',
        'description',
        'meta_title',
        'meta_description',
        'canonical_url',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }
}
