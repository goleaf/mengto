<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetProfileCompletionStep;
use App\Enums\PetProfileStatus;
use App\Models\PetProfile;

final class PetProfileCompletionPresenter
{
    /**
     * @return list<array{
     *     value: string,
     *     number: int,
     *     label: string,
     *     description: string,
     *     why: string,
     *     icon: string,
     *     active: bool,
     *     complete: bool,
     *     state: string,
     *     state_label: string
     * }>
     */
    public function present(
        PetProfile $profile,
        PetProfileCompletionStep $activeStep,
    ): array {
        return array_map(function (PetProfileCompletionStep $step) use ($activeStep, $profile): array {
            $complete = $this->isComplete($profile, $step);
            $state = $step === PetProfileCompletionStep::Preview
                ? 'ready'
                : ($complete ? 'saved' : 'optional');

            return [
                'value' => $step->value,
                'number' => $step->number(),
                'label' => $step->label(),
                'description' => $step->description(),
                'why' => $step->why(),
                'icon' => $step->icon(),
                'active' => $step === $activeStep,
                'complete' => $complete,
                'state' => $state,
                'state_label' => __("pet_profiles.completion.states.{$state}"),
            ];
        }, PetProfileCompletionStep::cases());
    }

    private function isComplete(
        PetProfile $profile,
        PetProfileCompletionStep $step,
    ): bool {
        $data = $profile->profile_data ?? [];

        return match ($step) {
            PetProfileCompletionStep::Basics => trim($profile->name) !== '',
            PetProfileCompletionStep::Photos => $this->existsAttribute($profile, 'primary_media_exists'),
            PetProfileCompletionStep::AgeAndSex => $profile->birth_date !== null
                || $profile->sex !== 'unknown'
                || $profile->reproductive_status !== 'unknown',
            PetProfileCompletionStep::BreedAndOrigin => $profile->taxon_id !== null
                || trim((string) $profile->breed) !== '',
            PetProfileCompletionStep::Appearance => $this->hasText($data, 'appearance_summary')
                || $this->hasText($data, 'identifying_marks'),
            PetProfileCompletionStep::Character => $this->hasText($data, 'story')
                || $this->hasText($data, 'temperament_summary'),
            PetProfileCompletionStep::SocialPreferences => $this->hasText($data, 'social_preferences')
                || $this->hasText($data, 'meeting_preferences'),
            PetProfileCompletionStep::Location => $this->hasText($data, 'location_label'),
            PetProfileCompletionStep::Owners => $this->existsAttribute($profile, 'managers_exists'),
            PetProfileCompletionStep::Privacy => $this->existsAttribute($profile, 'privacy_setting_exists'),
            PetProfileCompletionStep::Documents => $this->existsAttribute($profile, 'current_microchip_record_exists'),
            PetProfileCompletionStep::Preview => $profile->status !== PetProfileStatus::Draft,
        };
    }

    /** @param array<string, mixed> $data */
    private function hasText(array $data, string $key): bool
    {
        return trim(is_string($data[$key] ?? null) ? $data[$key] : '') !== '';
    }

    private function existsAttribute(PetProfile $profile, string $attribute): bool
    {
        return (bool) $profile->getAttribute($attribute);
    }
}
