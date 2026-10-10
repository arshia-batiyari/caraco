<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyOptionTranslation extends Model
{
    protected $fillable = ['survey_option_id', 'locale', 'label'];
}
