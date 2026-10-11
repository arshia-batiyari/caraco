<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeedbackFilter
{
    /**
     * @var list<string>
     */
    public const KEYS = ['type', 'rating', 'tour'];

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
            'type' => ['nullable', 'in:general,suggestion,compliment'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'tour' => ['nullable', 'integer', 'exists:tours,id'],
        ]);

        if ($validator->fails()) {
            return null;
        }

        return new self($validator->validated());
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
        if (filled($filters['type'] ?? null)) {
            $query->where('type', $filters['type']);
        }
        if (filled($filters['rating'] ?? null)) {
            $query->where('rating_overall', (int) $filters['rating']);
        }
        if (filled($filters['tour'] ?? null)) {
            $query->where('tour_id', (int) $filters['tour']);
        }

        return $query;
    }
}
