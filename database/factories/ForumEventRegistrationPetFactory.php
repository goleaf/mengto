<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumEventVerificationStatus;
use App\Models\ForumEventRegistration;
use App\Models\ForumEventRegistrationPet;
use App\Models\PetProfile;

/** @extends ApplicationFactory<ForumEventRegistrationPet> */
final class ForumEventRegistrationPetFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_event_registration_id' => ForumEventRegistration::factory(),
            'pet_profile_id' => null,
            'eligibility_status' => ForumEventVerificationStatus::NotAssessed,
            'verification_source' => ForumEventVerificationStatus::Unknown,
            'conditions' => null,
            'checked_in_at' => null,
            'checked_out_at' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (ForumEventRegistrationPet $registrationPet): void {
            if ($registrationPet->pet_profile_id !== null) {
                return;
            }

            $registration = ForumEventRegistration::query()
                ->with('user')
                ->findOrFail($registrationPet->forum_event_registration_id);

            $registrationPet->pet_profile_id = PetProfile::factory()
                ->for($registration->user)
                ->create()
                ->id;
        });
    }

    public function confirmed(): static
    {
        return $this->state(fn (): array => [
            'eligibility_status' => ForumEventVerificationStatus::Confirmed,
            'verification_source' => ForumEventVerificationStatus::ReportedByParticipant,
        ]);
    }
}
