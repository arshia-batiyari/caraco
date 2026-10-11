<?php

namespace App\Support;

use App\Models\AttractionCategory;
use App\Models\Destination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttractionFilter
{
    /**
     * @var list<string>
     */
    public const KEYS = ['q', 'country', 'city', 'category'];

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
            'q' => ['nullable', 'string', 'max:80'],
            'country' => ['nullable', 'integer', 'exists:destinations,id'],
            'city' => ['nullable', 'integer', 'exists:destinations,id'],
            'category' => ['nullable', 'integer', 'exists:attraction_categories,id'],
        ]);

        if ($validator->fails()) {
            return null;
        }

        $filters = $validator->validated();
        if (filled($filters['country'] ?? null)) {
            $country = Destination::query()->find($filters['country']);
            if ($country === null || $country->type !== 'country') {
                return null;
            }
        }
        if (filled($filters['city'] ?? null)) {
            $city = Destination::query()->find($filters['city']);
            if ($city === null || $city->type !== 'city') {
                return null;
            }
        }
        if (filled($filters['category'] ?? null) && ! AttractionCategory::query()->whereKey($filters['category'])->where('is_published', true)->exists()) {
            return null;
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
        if (filled($filters['q'] ?? null)) {
            $term = '%'.$filters['q'].'%';
            $locales = array_values(array_unique([app()->getLocale(), (string) config('app.fallback_locale')]));
            $query->whereHas('translations', function (Builder $inner) use ($term, $locales) {
                $inner->whereIn('locale', $locales)
                    ->where(function (Builder $match) use ($term) {
                        $match->where('title', 'like', $term)->orWhere('summary', 'like', $term);
                    });
            });
        }
        if (filled($filters['country'] ?? null)) {
            $query->whereIn('destination_id', Destination::idsWithin((int) $filters['country']));
        }
        if (filled($filters['city'] ?? null)) {
            $query->whereIn('destination_id', Destination::idsWithin((int) $filters['city']));
        }
        if (filled($filters['category'] ?? null)) {
            $query->where('attraction_category_id', (int) $filters['category']);
        }

        return $query;
    }
}
