<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslation;
use App\Models\Concerns\Publishes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attraction extends Model
{
    use HasLocaleTranslation, Publishes;

    protected $fillable = [
        'destination_id',
        'attraction_category_id',
        'classification',
        'unesco_designation',
        'unesco_verified',
        'inscribed_on',
        'unesco_reference',
        'latitude',
        'longitude',
        'is_published',
        'is_featured',
        'position',
        'cover_path',
    ];

    protected function casts(): array
    {
        return [
            'unesco_verified' => 'boolean',
            'inscribed_on' => 'date',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    protected function translationLabel(): string
    {
        return 'title';
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AttractionCategory::class, 'attraction_category_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(AttractionTranslation::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(AttractionImage::class)->orderBy('position');
    }

    public function tours(): BelongsToMany
    {
        return $this->belongsToMany(Tour::class, 'attraction_tour');
    }

    public function showsUnesco(): bool
    {
        return $this->unesco_designation === 'world_heritage' && $this->unesco_verified;
    }

    /**
     * @return array{city: ?string, country: ?string, destination: ?Destination}
     */
    public function publicPlace(): array
    {
        $destination = $this->destination;
        if (! $destination || ! $destination->is_published) {
            return ['city' => null, 'country' => null, 'destination' => null];
        }

        $city = $destination->type === 'city' ? $destination->publicTranslation()?->name : null;
        $country = $destination->type === 'country' ? $destination->publicTranslation()?->name : null;
        $cursor = $destination->parent;
        while ($cursor) {
            if ($cursor->is_published && $cursor->type === 'country') {
                $country = $cursor->publicTranslation()?->name;
                break;
            }
            $cursor = $cursor->parent;
        }

        return [
            'city' => $city,
            'country' => $country,
            'destination' => $destination,
        ];
    }
}
