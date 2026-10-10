<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutePointTranslation extends Model
{
    protected $fillable = ['locale', 'name', 'description'];
}
