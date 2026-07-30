<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DisputeStatus;
use App\Enums\OrderStatus;
use App\Models\Listing;
use App\Models\ListingReview;
use App\Models\Order;
use App\Models\OrderDispute;
use Illuminate\Support\Str;

class OrderPresenter
{
    public function __construct(
        private readonly ProfilePresenter $profiles,
        private readonly ForumActor $actor,
        private readonly ListingTaxonomy $taxonomy,
        private readonly LocaleFormatter $formatter,
    ) {}

    /** @return array<string, mixed> */
    public function show(Listing $listing, Order $order): array
    {
        $disputes = OrderDispute::query()
            ->select([
                'id', 'order_id', 'opened_by_key', 'opened_by_role', 'reason',
                'details', 'priority', 'status', 'resolution', 'resolved_at',
                'created_at',
            ])
            ->where('order_id', $order->id)
            ->latest('created_at')
            ->get()
            ->map(fn (OrderDispute $dispute): array => [
                'id' => $dispute->id,
                'opened_by_role' => Str::headline($dispute->opened_by_role),
                'reason' => Str::headline($dispute->reason),
                'details' => $dispute->details,
                'priority' => Str::headline($dispute->priority),
                'status' => $dispute->status->label(),
                'resolution' => $dispute->resolution,
                'created_label' => $this->formatter->relative($dispute->created_at),
            ])
            ->all();

        $review = ListingReview::query()
            ->select([
                'id', 'order_id', 'reviewer_name', 'is_verified_buyer',
                'item_rating', 'seller_rating', 'delivery_rating', 'body',
                'status', 'seller_reply', 'replied_at', 'created_at',
            ])
            ->where('order_id', $order->id)
            ->first();

        $isBuyer = $order->buyer_key === $this->actor->key();
        $hasActiveDispute = OrderDispute::query()
            ->where('order_id', $order->id)
            ->whereIn('status', [
                DisputeStatus::Open->value,
                DisputeStatus::NeedsEvidence->value,
                DisputeStatus::UnderReview->value,
                DisputeStatus::Appealed->value,
            ])
            ->exists();
        $item = $order->item_snapshot;
        $brandModel = implode(' ', array_filter([
            (string) ($item['brand'] ?? ''),
            (string) ($item['model'] ?? ''),
        ]));

        return [
            'owner' => $this->profiles->owner(),
            'page_title' => __('presentation.marketplace_order_title', ['reference' => $order->reference]),
            'active_section' => 'marketplace',
            'listing' => [
                'slug' => $listing->slug,
                'title' => $listing->title,
            ],
            'order' => [
                'reference' => $order->reference,
                'kind' => Str::headline($order->order_kind),
                'buyer_name' => $order->buyer_name,
                'seller_name' => $order->seller_name,
                'quantity' => $order->quantity,
                'unit_price' => $this->money($order->unit_price, $order->currency),
                'delivery_amount' => $this->money($order->delivery_amount, $order->currency),
                'deposit_amount' => $this->money($order->deposit_amount, $order->currency),
                'total_amount' => $this->money($order->total_amount, $order->currency),
                'delivery_method' => $this->taxonomy->deliveryOptions()[$order->delivery_method]
                    ?? Str::headline((string) $order->delivery_method),
                'public_delivery_area' => $order->public_delivery_area,
                'status' => $order->status->value,
                'status_label' => $order->status->label(),
                'payment_status' => $order->payment_status->value,
                'payment_label' => $order->payment_status->label(),
                'item' => $item,
                'item_brand_model' => $brandModel !== ''
                    ? $brandModel
                    : __('ui.not_specified_dc12bec5d7'),
                'item_condition_label' => Str::headline(
                    (string) ($item['condition'] ?? ''),
                ),
                'terms' => $order->terms_snapshot,
                'ordered_at' => $this->formatter->dateTime($order->ordered_at),
                'completed_at' => $this->formatter->dateTime($order->completed_at),
            ],
            'is_buyer' => $isBuyer,
            'can_dispute' => ! $hasActiveDispute
                && ! in_array($order->status, [OrderStatus::Cancelled, OrderStatus::Returned], true),
            'can_review' => $isBuyer
                && $order->status === OrderStatus::Completed
                && $review === null,
            'disputes' => $disputes,
            'review' => $review ? [
                'reviewer_name' => $review->reviewer_name,
                'verified' => $review->is_verified_buyer,
                'item_rating' => $review->item_rating,
                'seller_rating' => $review->seller_rating,
                'delivery_rating' => $review->delivery_rating,
                'body' => $review->body,
                'status' => $review->status->label(),
                'seller_reply' => $review->seller_reply,
                'created_label' => $this->formatter->relative($review->created_at),
            ] : null,
            'dispute_reasons' => $this->taxonomy->disputeReasons(),
        ];
    }

    private function money(float|string|null $amount, string $currency): string
    {
        return $this->formatter->currency((float) $amount, $currency);
    }
}
