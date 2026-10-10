<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NavigationItemTranslation extends Model
{
    protected $fillable = ['navigation_item_id', 'locale', 'label'];

    public function navigationItem(): BelongsTo
    {
        return $this->belongsTo(NavigationItem::class);
    }
}
