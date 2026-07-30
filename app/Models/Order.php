<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    private const ROUTE_COLUMNS = [
        'id', 'listing_id', 'reservation_id', 'buyer_id', 'seller_id',
        'reference', 'idempotency_key', 'buyer_key', 'buyer_name', 'seller_key',
        'seller_name', 'order_kind', 'quantity', 'unit_price', 'delivery_amount',
        'deposit_amount', 'total_amount', 'currency', 'delivery_method',
        'public_delivery_area', 'status', 'payment_status', 'item_snapshot',
        'terms_snapshot', 'ordered_at', 'paid_at', 'completed_at',
        'cancelled_at', 'created_at', 'updated_at',
    ];

    protected $fillable = [
        'listing_id', 'reservation_id', 'buyer_id', 'seller_id', 'reference',
        'idempotency_key', 'buyer_key', 'buyer_name', 'seller_key',
        'seller_name', 'order_kind', 'quantity', 'unit_price',
        'delivery_amount', 'deposit_amount', 'total_amount', 'currency',
        'delivery_method', 'public_delivery_area', 'status', 'payment_status',
        'item_snapshot', 'terms_snapshot', 'ordered_at', 'paid_at',
        'completed_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'unit_price' => 'decimal:2',
            'delivery_amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'item_snapshot' => 'array',
            'terms_snapshot' => 'array',
            'ordered_at' => 'datetime',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return parent::resolveRouteBindingQuery($query, $value, $field)
            ->select(self::ROUTE_COLUMNS);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(OrderDispute::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(ListingReview::class);
    }
}
