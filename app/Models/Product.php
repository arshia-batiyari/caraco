<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'booth_id', 'category_id', 'name', 'description', 'sku', 'slug', 'price', 'discount_percent', 'stock', 'weight_grams', 'video_path', 'video_status', 'status',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->name['en'] ?? $product->name['fa'] ?? 'product');
            }
        });
    }

    /**
     * برای URL سئوپسند و منحصربه‌فرد. چون خیلی از غرفه‌دارها فقط اسم فارسی
     * وارد می‌کنن (که مستقیم به slug لاتین قابل تبدیل نیست)، همیشه یک پسوند
     * کوتاه تصادفی هم اضافه می‌شه تا هم یکتا بمونه هم نیازی به حلقه retry نباشه.
     */
    protected static function generateUniqueSlug(string $source): string
    {
        $base = Str::slug($source);
        if ($base === '') {
            $base = 'product';
        }

        return $base.'-'.Str::lower(Str::random(6));
    }

    /** برای اینکه route model binding و route() helper به‌جای id از slug استفاده کنن */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function booth(): BelongsTo
    {
        return $this->belongsTo(Booth::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function getLocalizedNameAttribute(): string
    {
        return $this->name[app()->getLocale()] ?? $this->name['fa'] ?? '';
    }

    /**
     * آدرس اولین عکس محصول. اگر عکسی آپلود نشده باشه، یک تصویر جایگزین
     * نمایشی (بر اساس دسته‌بندی محصول) برمی‌گردونه تا صفحه هیچ‌وقت جای خالی
     * نداشته باشه.
     */
    public function getImageUrlAttribute(): string
    {
        $first = $this->relationLoaded('images') ? $this->images->first() : $this->images()->first();

        if ($first) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($first->path);
        }

        $rawSlug = $this->category?->slug ?? 'default';
        $slug = match (true) {
            str_starts_with($rawSlug, 'real-estate') => 'real-estate',
            default => $rawSlug,
        };

        return asset('images/placeholders/products/'.(
            file_exists(public_path('images/placeholders/products/'.$slug.'.jpg')) ? $slug : 'default'
        ).'.jpg');
    }

    public function getFinalPriceAttribute(): int
    {
        return (int) round($this->price * (100 - ($this->discount_percent ?? 0)) / 100);
    }

    public function getVideoUrlAttribute(): ?string
    {
        return $this->video_path && $this->video_status === 'approved' ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->video_path) : null;
    }

    public function scopeInStock($query)
    {
        return $query->where('status', 'active')->where('stock', '>', 0);
    }
}
