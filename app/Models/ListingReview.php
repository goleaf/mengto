<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReviewStatus;
use Database\Factories\ListingReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $body
 * @property Carbon|null $created_at
 * @property int|null $delivery_rating
 * @property int $id
 * @property bool $is_verified_buyer
 * @property int $item_rating
 * @property-read Listing|null $listing
 * @property int $listing_id
 * @property-read Order|null $order
 * @property int $order_id
 * @property Carbon|null $replied_at
 * @property string $reviewer_key
 * @property string $reviewer_name
 * @property int $seller_rating
 * @property string|null $seller_reply
 * @property ReviewStatus $status
 * @property Carbon|null $updated_at
 */
class ListingReview extends Model
{
    /** @use HasFactory<ListingReviewFactory> */
    use HasFactory;

    protected $fillable = [
        'listing_id', 'order_id', 'reviewer_key', 'reviewer_name',
        'is_verified_buyer', 'item_rating', 'seller_rating', 'delivery_rating',
        'body', 'status', 'seller_reply', 'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReviewStatus::class,
            'is_verified_buyer' => 'boolean',
            'replied_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<\App\Models\Listing, $this>*/
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /** @return BelongsTo<\App\Models\Order, $this>*/
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
