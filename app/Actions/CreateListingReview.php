<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Enums\ReviewStatus;
use App\Models\AuditLog;
use App\Models\ListingReview;
use App\Models\Order;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateListingReview
{
    public function __construct(private readonly ForumActor $actor) {}

    /**
     * @param  array{item_rating: int, seller_rating: int, delivery_rating?: int|null, body: string}  $data
     */
    public function handle(Order $order, array $data): ListingReview
    {
        return DB::transaction(function () use ($order, $data): ListingReview {
            $lockedOrder = Order::query()
                ->select([
                    'id', 'listing_id', 'buyer_key', 'buyer_name', 'status',
                ])
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($lockedOrder->buyer_key !== $this->actor->key()) {
                throw ValidationException::withMessages([
                    'order' => __('messages.only_the_buyer_can_review_this_order'),
                ]);
            }

            if ($lockedOrder->status !== OrderStatus::Completed) {
                throw ValidationException::withMessages([
                    'order' => __('messages.complete_the_order_before_leaving_a_review'),
                ]);
            }

            if (ListingReview::query()->where('order_id', $lockedOrder->id)->exists()) {
                throw ValidationException::withMessages([
                    'order' => __('messages.this_order_already_has_a_review'),
                ]);
            }

            $review = ListingReview::query()->create([
                'listing_id' => $lockedOrder->listing_id,
                'order_id' => $lockedOrder->id,
                'reviewer_key' => $lockedOrder->buyer_key,
                'reviewer_name' => $lockedOrder->buyer_name,
                'is_verified_buyer' => true,
                'item_rating' => $data['item_rating'],
                'seller_rating' => $data['seller_rating'],
                'delivery_rating' => $data['delivery_rating'] ?? null,
                'body' => $data['body'],
                'status' => ReviewStatus::Published,
            ]);

            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => 'marketplace-buyer',
                'action' => 'listing.review-created',
                'target_type' => ListingReview::class,
                'target_id' => (string) $review->id,
                'metadata' => [
                    'listing_id' => $review->listing_id,
                    'order_id' => $review->order_id,
                    'verified_buyer' => true,
                ],
            ]);

            return $review;
        });
    }
}
