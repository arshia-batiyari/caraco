<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourImage extends Model
{
    protected $fillable = ['tour_id', 'path', 'position', 'is_cover'];

    protected function casts(): array
    {
        return ['is_cover' => 'boolean'];
    }
}
