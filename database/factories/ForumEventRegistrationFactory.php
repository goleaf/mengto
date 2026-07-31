<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumEventFormat;
use App\Enums\ForumEventPhotoConsent;
use App\Enums\ForumEventRegistrationStatus;
use App\Models\ForumEvent;
use App\Models\ForumEventRegistration;
use App\Models\PetProfile;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumEventRegistration>
 */
final class ForumEventRegistrationFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_event_id' => ForumEvent::factory(),
            'user_id' => User::factory(),
            'pet_profile_id' => null,
            'stable_key' => 'event-registration-'.Str::lower((string) Str::ulid()),
            'idempotency_key' => (string) Str::uuid(),
            'status' => ForumEventRegistrationStatus::Confirmed,
            'attendance_format' => ForumEventFormat::Physical,
            'guest_count' => 0,
            'requirements_note' => null,
            'photo_consent' => ForumEventPhotoConsent::AskFirst,
            'requirements_accepted' => true,
            'waitlist_position' => null,
            'check_in_method' => null,
            'checked_in_at' => null,
            'cancelled_at' => null,
            'cancellation_reason_code' => null,
            'lock_version' => 0,
        ];
    }

    public function forPet(?PetProfile $pet = null): static
    {
        $pet ??= PetProfile::factory()->create();

        return $this->for($pet->user, 'user')->for($pet, 'petProfile');
    }

    public function pending(): static
    {
        return $this->state(fn (): array => [
            'status' => ForumEventRegistrationStatus::Pending,
        ]);
    }

    public function waitlisted(int $position = 1): static
    {
        return $this->state(fn (): array => [
            'status' => ForumEventRegistrationStatus::Waitlisted,
            'waitlist_position' => $position,
        ]);
    }

    public function checkedIn(): static
    {
        return $this->state(fn (): array => [
            'status' => ForumEventRegistrationStatus::CheckedIn,
            'check_in_method' => 'manual',
            'checked_in_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => ForumEventRegistrationStatus::Cancelled,
            'cancelled_at' => now(),
            'cancellation_reason_code' => 'participant-cancelled',
        ]);
    }
}
