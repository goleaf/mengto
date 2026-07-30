<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use Database\Factories\ListingReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
