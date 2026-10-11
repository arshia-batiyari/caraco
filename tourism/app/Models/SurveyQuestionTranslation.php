<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyQuestionTranslation extends Model
{
    protected $fillable = ['survey_question_id', 'locale', 'prompt'];
}
