<?php

namespace App\Support;

use App\Models\Tour;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TourFilter
{
    /**
     * @var list<string>
     */
    public const KEYS = [
        'scope',
        'destination',
        'city',
        'depart_from',
        'depart_to',
        'days_min',
        'days_max',
        'price_min',
        'price_max',
        'currency',
        'transport',
        'category',
        'discounted',
        'available',
    ];

    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(private array $filters) {}

    public static function fromRequest(Request $request): ?self
    {
        $input = [];
        foreach (self::KEYS as $key) {
            $value = $request->query($key);
            $input[$key] = is_string($value) ? trim($value) : $value;
            if ($input[$key] === '') {
                $input[$key] = null;
            }
        }

        $validator = Validator::make($input, [
            'scope' => ['nullable', 'in:domestic,international'],
            'destination' => ['nullable', 'integer', 'exists:destinations,id'],
            'city' => ['nullable', 'integer', 'exists:destinations,id'],
            'depart_from' => ['nullable', 'date'],
            'depart_to' => ['nullable', 'date'],
            'days_min' => ['nullable', 'integer', 'min:1', 'max:365'],
            'days_max' => ['nullable', 'integer', 'min:1', 'max:365'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'regex:/^[A-Za-z]{3}$/'],
            'transport' => ['nullable', 'integer', 'exists:transport_options,id'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
            'discounted' => ['nullable', 'in:1'],
            'available' => ['nullable', 'in:1'],
        ]);

        if ($validator->fails()) {
            return null;
        }

        $filters = $validator->validated();
        if (($filters['price_min'] ?? null) !== null || ($filters['price_max'] ?? null) !== null) {
            if (! filled($filters['currency'] ?? null)) {
                return null;
            }
        }
        if (isset($filters['days_min'], $filters['days_max']) && (int) $filters['days_min'] > (int) $filters['days_max']) {
            return null;
        }
        if (isset($filters['price_min'], $filters['price_max']) && (float) $filters['price_min'] > (float) $filters['price_max']) {
            return null;
        }
        if (isset($filters['depart_from'], $filters['depart_to']) && $filters['depart_from'] > $filters['depart_to']) {
            return null;
        }

        if (filled($filters['currency'] ?? null)) {
            $filters['currency'] = strtoupper((string) $filters['currency']);
        }

        return new self($filters);
    }

    public static function requested(Request $request): bool
    {
        foreach (self::KEYS as $key) {
            if ($request->query($key) !== null && $request->query($key) !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function values(): array
    {
        return $this->filters;
    }

    public function apply(Builder $query): Builder
    {
        $filters = $this->filters;

        if (filled($filters['scope'] ?? null)) {
            $query->where('scope', $filters['scope']);
        }
        if (filled($filters['destination'] ?? null)) {
            $query->forDestination((int) $filters['destination']);
        }
        if (filled($filters['city'] ?? null)) {
            $query->forDestination((int) $filters['city']);
        }
        if (filled($filters['category'] ?? null)) {
            $query->where('category_id', (int) $filters['category']);
        }
        if (filled($filters['transport'] ?? null)) {
            $query->whereHas('transports', fn (Builder $inner) => $inner->where('transport_options.id', (int) $filters['transport']));
        }
        if (filled($filters['days_min'] ?? null)) {
            $query->where('duration_days', '>=', (int) $filters['days_min']);
        }
        if (filled($filters['days_max'] ?? null)) {
            $query->where('duration_days', '<=', (int) $filters['days_max']);
        }
        if (($filters['discounted'] ?? null) === '1') {
            $query->discounted();
        }
        if (($filters['available'] ?? null) === '1') {
            $query->available();
        }
        if (filled($filters['depart_from'] ?? null) || filled($filters['depart_to'] ?? null)) {
            $query->whereHas('departures', function (Builder $inner) use ($filters) {
                $inner->where('is_published', true);
                if (filled($filters['depart_from'] ?? null)) {
                    $inner->whereDate('departure_date', '>=', $filters['depart_from']);
                }
                if (filled($filters['depart_to'] ?? null)) {
                    $inner->whereDate('departure_date', '<=', $filters['depart_to']);
                }
            });
        }
        if (filled($filters['currency'] ?? null) && (($filters['price_min'] ?? null) !== null || ($filters['price_max'] ?? null) !== null)) {
            $min = (float) ($filters['price_min'] ?? 0);
            $max = (float) ($filters['price_max'] ?? 9999999999);
            $query->pricedBetween((string) $filters['currency'], $min, $max);
        }

        return $query;
    }
}
