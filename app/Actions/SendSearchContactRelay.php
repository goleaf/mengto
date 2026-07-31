<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\SearchContactRelayData;
use App\Models\SearchCase;
use App\Models\SearchCaseEvent;
use App\Models\SearchContactRelay;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class SendSearchContactRelay
{
    public function __construct(private readonly Gate $gate) {}

    public function handle(
        User $sender,
        SearchCase $searchCase,
        SearchContactRelayData $data,
    ): SearchContactRelay {
        $this->gate->forUser($sender)->authorize('contact', $searchCase);

        return DB::transaction(function () use ($data, $searchCase, $sender): SearchContactRelay {
            $existing = SearchContactRelay::query()
                ->where('idempotency_key', $data->idempotencyKey)
                ->first();

            if ($existing !== null) {
                if (
                    $existing->search_case_id !== $searchCase->id
                    || $existing->sender_user_id !== $sender->id
                ) {
                    throw ValidationException::withMessages([
                        'idempotency_key' => __('lost_found.validation.relay_idempotency_conflict'),
                    ]);
                }

                return $existing;
            }

            $lockedCase = SearchCase::query()
                ->select([
                    'id', 'owner_id', 'owner_key', 'coordinator_key', 'status',
                    'moderation_status', 'visibility', 'archived_at',
                ])
                ->lockForUpdate()
                ->findOrFail($searchCase->id);
            $this->gate->forUser($sender)->authorize('contact', $lockedCase);

            if ($lockedCase->owner_id === null) {
                throw ValidationException::withMessages([
                    'message' => __('lost_found.validation.relay_unavailable'),
                ]);
            }

            $relay = SearchContactRelay::query()->create([
                'search_case_id' => $lockedCase->id,
                'sender_user_id' => $sender->id,
                'recipient_user_id' => $lockedCase->owner_id,
                'idempotency_key' => $data->idempotencyKey,
                'purpose' => $data->purpose,
                'message' => $data->message,
                'status' => 'submitted',
            ]);

            SearchCaseEvent::query()->create([
                'search_case_id' => $lockedCase->id,
                'actor_user_id' => $sender->id,
                'event_type' => 'contact-relay-submitted',
                'current_status' => $lockedCase->status->value,
                'reason_translation_key' => 'lost_found.events.contact_relay_submitted',
                'idempotency_key' => (string) Str::uuid(),
                'metadata' => [
                    'relay_id' => $relay->id,
                    'purpose' => $relay->purpose,
                ],
            ]);

            return $relay;
        }, 3);
    }
}
