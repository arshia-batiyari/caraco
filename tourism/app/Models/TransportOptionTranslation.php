<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportOptionTranslation extends Model
{
    protected $fillable = ['locale', 'name', 'description'];
}
