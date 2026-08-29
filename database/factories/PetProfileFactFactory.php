<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PetEvidenceStatus;
use App\Enums\PetProfileVisibility;
use App\Models\PetProfile;
use App\Models\PetProfileFact;

/** @extends ApplicationFactory<PetProfileFact> */
final class PetProfileFactFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $fact = fake()->randomElement([
            [
                'key' => 'birth-date',
                'value' => ['date' => now()->subYears(4)->toDateString()],
                'precision' => 'exact',
            ],
            [
                'key' => 'breed',
                'value' => ['label' => 'Mixed breed', 'classification' => 'owner-reported'],
                'precision' => 'estimated',
            ],
            [
                'key' => 'microchip-status',
                'value' => ['status' => 'chipped'],
                'precision' => 'exact',
            ],
        ]);
        $valueHash = hash('sha256', json_encode($fact['value'], JSON_THROW_ON_ERROR));

        return [
            'pet_profile_id' => PetProfile::factory(),
            'fact_key' => $fact['key'],
            'value' => $fact['value'],
            'normalized_value_hash' => $valueHash,
            'precision' => $fact['precision'],
            'source_type' => 'owner',
            'source_reference' => 'factory owner report',
            'author_user_id' => null,
            'verification_status' => PetEvidenceStatus::Unverified,
            'visibility' => PetProfileVisibility::Private,
            'is_current' => true,
            'current_key' => fn (array $attributes): string => "pet:{$attributes['pet_profile_id']}:fact:{$attributes['fact_key']}",
            'recorded_at' => now(),
            'metadata' => ['captured_by' => 'factory', 'review_required' => false],
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(static function (PetProfileFact $fact): void {
            if ($fact->pet_profile_id !== null) {
                $fact->author_user_id = PetProfile::query()
                    ->whereKey($fact->pet_profile_id)
                    ->value('user_id');
            }
        });
    }
}
