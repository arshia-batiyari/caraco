<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = [
        'parent_id', 'name', 'description', 'slug', 'icon', 'image', 'hero_image', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',        // {"fa": "...", "en": "..."}
            'description' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function booths(): BelongsToMany
    {
        return $this->belongsToMany(Booth::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** نام دسته‌بندی به زبان جاری برنامه */
    public function getLocalizedNameAttribute(): string
    {
        return $this->name[app()->getLocale()] ?? $this->name['fa'] ?? '';
    }


    /**
     * تصویر نمایشی دسته‌بندی برای کارت‌های لندینگ/هیرو.
     * اگر فایل خاصی برای این دسته موجود نباشد، یک تصویر fallback برمی‌گرداند.
     */
    public function getHeroImageUrlAttribute(): string
    {
        if ($this->hero_image) { return \Storage::disk('public')->url($this->hero_image); }

        if ($this->image) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($this->image);
        }

        $slug = match ($this->slug) {
            'building-materials' => 'building-cement',
            'handicrafts' => 'handicrafts-rugs',
            default => $this->slug,
        };

        return asset('images/placeholders/banners/'.(
            file_exists(public_path('images/placeholders/banners/'.$slug.'.webp')) ? $slug : 'default'
        ).'.webp');
    }

    /** فقط دسته‌بندی‌های ریشه (بدون parent) */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * تعداد کل غرفه‌ها شامل زیردسته‌ها
     * برای نمایش صحیح روی صفحه اصلی (مثلاً مصالح ساختمانی = 4 غرفه)
     */
    public function getBoothsRecursiveCountAttribute(): int
    {
        if ($this->relationLoaded('children') && $this->children->isNotEmpty()) {
            $ids = $this->children->pluck('id')->push($this->id);
            return Booth::approved()
                ->whereHas('categories', fn($q) => $q->whereIn('categories.id', $ids))
                ->count();
        }
        return (int) ($this->booths_count ?? $this->booths()->count());
    }

    /** نمایش عدد غرفه‌ها (بازگشت به تعداد بازگشتی برای استفاده در Blade) */
    public function getDisplayBoothsCountAttribute(): int
    {
        return $this->boothsRecursiveCount;
    }
}