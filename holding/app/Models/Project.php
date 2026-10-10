<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasLocaleTranslation;

    protected $fillable = ['is_published', 'position', 'image_path'];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ProjectTranslation::class);
    }
}
