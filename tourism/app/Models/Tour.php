<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslation;
use App\Support\TourPrice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tour extends Model
{
    use HasLocaleTranslation;

    protected $fillable = [
        'scope',
        'destination_id',
        'category_id',
        'travel_style_id',
        'is_published',
        'is_featured',
        'position',
        'cover_path',
        'currency',
        'base_price',
        'discount_price',
        'discount_percent',
        'discount_starts_at',
        'discount_ends_at',
        'duration_days',
        'duration_nights',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'base_price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'discount_percent' => 'integer',
            'discount_starts_at' => 'date',
            'discount_ends_at' => 'date',
            'duration_days' => 'integer',
            'duration_nights' => 'integer',
        ];
    }

    public function hasDiscountValue(): bool
    {
        return ($this->discount_price !== null && $this->discount_percent === null)
            || ($this->discount_percent !== null && $this->discount_price === null);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function travelStyle(): BelongsTo
    {
        return $this->belongsTo(TravelStyle::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(TourTranslation::class);
    }

    public function departures(): HasMany
    {
        return $this->hasMany(TourDeparture::class)->orderBy('departure_date');
    }

    public function transports(): BelongsToMany
    {
        return $this->belongsToMany(TransportOption::class, 'tour_transport')
            ->withPivot('position')
            ->orderByPivot('position');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'tour_service')
            ->withPivot('position')
            ->orderByPivot('position');
    }

    public function stays(): HasMany
    {
        return $this->hasMany(TourHotel::class)->orderBy('position');
    }

    public function images(): HasMany
    {
        return $this->hasMany(TourImage::class)->orderBy('position');
    }

    public function days(): HasMany
    {
        return $this->hasMany(ItineraryDay::class)->orderBy('day_number');
    }

    public function routePoints(): HasMany
    {
        return $this->hasMany(RoutePoint::class)->orderBy('position');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class)->orderBy('position');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeVisibleInLocale(Builder $query, ?string $locale = null): Builder
    {
        $locale = $locale ?? app()->getLocale();
        $fallback = (string) config('app.fallback_locale');

        return $query->where(function (Builder $query) use ($locale, $fallback) {
            $query->whereHas('translations', fn (Builder $inner) => $inner->where('locale', $locale)->where('title', '!=', ''))
                ->orWhereHas('translations', fn (Builder $inner) => $inner->where('locale', $fallback)->where('title', '!=', ''));
        });
    }

    public function scopeAvailable(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where(function (Builder $query) use ($today) {
            $query->whereDoesntHave('departures', fn (Builder $inner) => $inner->where('is_published', true))
                ->orWhereHas('departures', fn (Builder $inner) => $inner->where('is_published', true)->whereDate('departure_date', '>=', $today));
        });
    }

    public function scopeDiscounted(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->whereNotNull('base_price')
            ->where(function (Builder $query) {
                $query->where(function (Builder $query) {
                    $query->whereNotNull('discount_price')
                        ->whereNull('discount_percent')
                        ->whereColumn('discount_price', '<', 'base_price');
                })->orWhere(function (Builder $query) {
                    $query->whereNotNull('discount_percent')->whereNull('discount_price');
                });
            })
            ->where(function (Builder $query) use ($today) {
                $query->whereNull('discount_starts_at')->orWhereDate('discount_starts_at', '<=', $today);
            })
            ->where(function (Builder $query) use ($today) {
                $query->whereNull('discount_ends_at')->orWhereDate('discount_ends_at', '>=', $today);
            });
    }

    public function scopePricedBetween(Builder $query, string $currency, float $min, float $max): Builder
    {
        return $query->where('currency', strtoupper($currency))
            ->whereNotNull('base_price')
            ->whereRaw(TourPrice::betweenExpression(), [
                ...TourPrice::expressionBindings(),
                number_format($min, 2, '.', ''),
                number_format($max, 2, '.', ''),
            ]);
    }

    public function scopeForDestination(Builder $query, int $destinationId): Builder
    {
        return $query->whereIn('destination_id', Destination::idsWithin($destinationId));
    }

    public function nextDeparture(): ?TourDeparture
    {
        $rows = $this->relationLoaded('departures')
            ? $this->departures
            : $this->departures()->get();
        $published = $rows->where('is_published', true)->sortBy('departure_date')->values();
        $today = now()->toDateString();

        return $published->first(fn (TourDeparture $row) => $row->departure_date->toDateString() >= $today)
            ?? $published->first();
    }
}
