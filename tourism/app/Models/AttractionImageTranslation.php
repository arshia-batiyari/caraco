<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttractionImageTranslation extends Model
{
    protected $fillable = ['attraction_image_id', 'locale', 'alt', 'caption'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(AttractionImage::class, 'attraction_image_id');
    }
}
