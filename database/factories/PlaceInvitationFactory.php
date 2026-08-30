<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceInvitationStatus;
use App\Models\Place;
use App\Models\PlaceInvitation;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceInvitation> */
final class PlaceInvitationFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'invitation_key' => (string) Str::uuid(),
            'place_id' => Place::factory(),
            'sender_user_id' => User::factory(),
            'recipient_user_id' => User::factory(),
            'responded_by_user_id' => null,
            'revoked_by_user_id' => null,
            'status' => PlaceInvitationStatus::Pending,
            'visibility' => 'private',
            'message' => null,
            'proposed_at' => now()->addDay(),
            'sent_at' => now(),
            'expires_at' => now()->addDays(7),
            'responded_at' => null,
            'revoked_at' => null,
            'reason_code' => null,
            'idempotency_key' => hash('sha256', (string) Str::uuid()),
            'response_key' => null,
            'revocation_key' => null,
            'open_key' => hash('sha256', (string) Str::uuid()),
            'lock_version' => 0,
        ];
    }
}
