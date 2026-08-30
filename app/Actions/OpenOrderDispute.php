<?php

namespace App\Actions;

use App\Enums\DisputeStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OrderDispute;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OpenOrderDispute
{
    public function __construct(private readonly ForumActor $actor) {}

    /** @param array{reason: string, details: string} $data */
    public function handle(Order $order, array $data): OrderDispute
    {
        return DB::transaction(function () use ($order, $data): OrderDispute {
            $lockedOrder = Order::query()
                ->select([
                    'id', 'listing_id', 'buyer_key', 'seller_key', 'status',
                    'payment_status',
                ])
                ->lockForUpdate()
                ->findOrFail($order->id);

            $role = match ($this->actor->key()) {
                $lockedOrder->buyer_key => 'buyer',
                $lockedOrder->seller_key => 'seller',
                default => null,
            };

            if ($role === null) {
                throw ValidationException::withMessages([
                    'order' => __('messages.only_order_participants_can_open_a_dispute'),
                ]);
            }

            $hasActiveDispute = OrderDispute::query()
                ->where('order_id', $lockedOrder->id)
                ->whereIn('status', [
                    DisputeStatus::Open->value,
                    DisputeStatus::NeedsEvidence->value,
                    DisputeStatus::UnderReview->value,
                    DisputeStatus::Appealed->value,
                ])
                ->exists();

            if ($hasActiveDispute) {
                throw ValidationException::withMessages([
                    'order' => __('messages.an_active_dispute_already_exists_for_this_order'),
                ]);
            }

            $priority = in_array($data['reason'], [
                'counterfeit',
                'fraud',
                'dangerous-product',
                'animal-welfare',
            ], true) ? 'high' : 'normal';

            $dispute = OrderDispute::query()->create([
                'order_id' => $lockedOrder->id,
                'listing_id' => $lockedOrder->listing_id,
                'opened_by_key' => $this->actor->key(),
                'opened_by_role' => $role,
                'reason' => $data['reason'],
                'details' => $data['details'],
                'evidence' => [],
                'priority' => $priority,
                'status' => DisputeStatus::Open,
            ]);

            $lockedOrder->update([
                'status' => OrderStatus::Disputed,
                'payment_status' => $lockedOrder->payment_status === PaymentStatus::NotRequired
                    ? PaymentStatus::NotRequired
                    : PaymentStatus::Disputed,
            ]);

            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => 'marketplace-'.$role,
                'action' => 'order.dispute-opened',
                'target_type' => Order::class,
                'target_id' => (string) $lockedOrder->id,
                'metadata' => [
                    'dispute_id' => $dispute->id,
                    'reason' => $dispute->reason,
                    'priority' => $dispute->priority,
                ],
            ]);

            return $dispute;
        });
    }
}
