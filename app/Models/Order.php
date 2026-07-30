<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property-read User|null $buyer
 * @property int|null $buyer_id
 * @property string $buyer_key
 * @property string $buyer_name
 * @property Carbon|null $cancelled_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property string $currency
 * @property numeric-string $delivery_amount
 * @property string $delivery_method
 * @property numeric-string $deposit_amount
 * @property-read Collection<int, OrderDispute> $disputes
 * @property int $id
 * @property string $idempotency_key
 * @property array<array-key, mixed> $item_snapshot
 * @property-read Listing|null $listing
 * @property int $listing_id
 * @property string $order_kind
 * @property Carbon $ordered_at
 * @property Carbon|null $paid_at
 * @property PaymentStatus $payment_status
 * @property string|null $public_delivery_area
 * @property int $quantity
 * @property string $reference
 * @property-read Reservation|null $reservation
 * @property int $reservation_id
 * @property-read ListingReview|null $review
 * @property-read User|null $seller
 * @property int|null $seller_id
 * @property string $seller_key
 * @property string $seller_name
 * @property OrderStatus $status
 * @property array<array-key, mixed> $terms_snapshot
 * @property numeric-string $total_amount
 * @property numeric-string|null $unit_price
 * @property Carbon|null $updated_at
 */
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

    /** @return BelongsTo<\App\Models\Listing, $this>*/
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /** @return BelongsTo<\App\Models\Reservation, $this>*/
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /** @return BelongsTo<\App\Models\User, $this>*/
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /** @return BelongsTo<\App\Models\User, $this>*/
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /** @return HasMany<\App\Models\OrderDispute, $this>*/
    public function disputes(): HasMany
    {
        return $this->hasMany(OrderDispute::class);
    }

    /** @return HasOne<\App\Models\ListingReview, $this>*/
    public function review(): HasOne
    {
        return $this->hasOne(ListingReview::class);
    }
}
