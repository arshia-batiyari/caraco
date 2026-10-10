<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    protected $fillable = [
        'order_id', 'booth_id', 'shipping_cost', 'carrier', 'tracking_code', 'status', 'shipped_at', 'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function booth(): BelongsTo
    {
        return $this->belongsTo(Booth::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function markShipped(string $carrier, string $trackingCode): void
    {
        $this->update([
            'carrier' => $carrier,
            'tracking_code' => $trackingCode,
            'status' => 'shipped',
            'shipped_at' => now(),
        ]);

        $this->items()->update(['item_status' => 'shipped']);
    }

    public function markDelivered(): void
    {
        $this->update(['status' => 'delivered', 'delivered_at' => now()]);
        $this->items()->update(['item_status' => 'delivered']);
    }
}
