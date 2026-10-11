<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public $timestamps = false;

    protected static function booted(): void
    {
        $forget = function (): void {
            Cache::forget('karako.settings');
        };

        static::saved($forget);
        static::deleted($forget);
    }

    public static function getValue(string $key, ?string $default = null): ?string
    {
        $settings = Cache::rememberForever('karako.settings', function (): array {
            return static::query()->pluck('value', 'key')->all();
        });

        $value = $settings[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : $default;
    }

    public static function putValue(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value !== null && $value !== '' ? $value : null]
        );
    }
}
