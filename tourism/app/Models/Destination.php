<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Destination extends Model
{
    use HasLocaleTranslation;

    protected $fillable = [
        'parent_id',
        'type',
        'is_published',
        'is_featured',
        'position',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    protected function translationLabel(): string
    {
        return 'name';
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(DestinationTranslation::class);
    }

    /**
     * @return list<int>
     */
    public static function idsWithin(int $id): array
    {
        $childIds = static::query()->where('parent_id', $id)->pluck('id');
        $grandchildIds = static::query()->whereIn('parent_id', $childIds)->pluck('id');

        return $childIds->merge($grandchildIds)->push($id)->unique()->map(fn ($value) => (int) $value)->all();
    }
}
