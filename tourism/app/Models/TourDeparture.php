<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourDeparture extends Model
{
    protected $fillable = ['tour_id', 'departure_date', 'return_date', 'is_published'];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'return_date' => 'date',
            'is_published' => 'boolean',
        ];
    }
}
