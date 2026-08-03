<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumEventFormat;
use App\Enums\ForumEventPhotoConsent;
use App\Enums\ForumEventRegistrationStatus;
use App\Models\ForumEvent;
use App\Models\ForumEventOccurrence;
use App\Models\ForumEventRegistration;
use App\Models\ForumEventRegistrationPet;
use App\Models\ForumEventVersion;
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
            'forum_event_occurrence_id' => null,
            'forum_event_version_id' => null,
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
            'accepted_snapshot' => null,
            'accepted_snapshot_checksum' => null,
            'locale' => 'en',
            'timezone' => 'UTC',
            'submitted_at' => now(),
            'confirmed_at' => now(),
            'checked_out_at' => null,
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

    public function draft(): static
    {
        return $this->withStatus(ForumEventRegistrationStatus::Draft);
    }

    public function submitted(): static
    {
        return $this->withStatus(ForumEventRegistrationStatus::Submitted);
    }

    public function incomplete(): static
    {
        return $this->withStatus(ForumEventRegistrationStatus::Incomplete);
    }

    public function documentsRequired(): static
    {
        return $this->withStatus(ForumEventRegistrationStatus::DocumentsRequired);
    }

    public function approved(): static
    {
        return $this->withStatus(ForumEventRegistrationStatus::Approved);
    }

    public function conditionallyApproved(): static
    {
        return $this->withStatus(ForumEventRegistrationStatus::ApprovedWithConditions);
    }

    public function confirmed(): static
    {
        return $this->withStatus(ForumEventRegistrationStatus::Confirmed);
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

    public function attended(): static
    {
        return $this->state(fn (): array => [
            'status' => ForumEventRegistrationStatus::Attended,
            'check_in_method' => 'manual',
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => now(),
        ]);
    }

    public function noShow(): static
    {
        return $this->withStatus(ForumEventRegistrationStatus::NoShow);
    }

    public function withdrawn(): static
    {
        return $this->withStatus(ForumEventRegistrationStatus::Withdrawn);
    }

    public function rejected(): static
    {
        return $this->withStatus(ForumEventRegistrationStatus::Rejected);
    }

    public function refunded(): static
    {
        return $this->withStatus(ForumEventRegistrationStatus::Refunded);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => ForumEventRegistrationStatus::Cancelled,
            'cancelled_at' => now(),
            'cancellation_reason_code' => 'participant-cancelled',
        ]);
    }

    public function forOccurrence(?ForumEventOccurrence $occurrence = null): static
    {
        $selected = $occurrence ?? ForumEventOccurrence::factory()->create();

        return $this
            ->for($selected->event, 'event')
            ->for($selected, 'occurrence');
    }

    public function forVersion(?ForumEventVersion $version = null): static
    {
        $selected = $version ?? ForumEventVersion::factory()->create();

        return $this
            ->for($selected->event, 'event')
            ->for($selected, 'version');
    }

    public function withPet(?PetProfile $pet = null): static
    {
        return $this->afterCreating(function (ForumEventRegistration $registration) use ($pet): void {
            ForumEventRegistrationPet::factory()
                ->confirmed()
                ->for($registration, 'registration')
                ->for($pet ?? PetProfile::factory()->for($registration->user)->create(), 'petProfile')
                ->create();
        });
    }

    private function withStatus(ForumEventRegistrationStatus $status): static
    {
        return $this->state(fn (): array => ['status' => $status]);
    }
}
