<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\AdoptionPlacementType;

final readonly class AdoptionApplicationData
{
    /**
     * @param  array<string, string>  $privateProfile
     */
    public function __construct(
        public AdoptionPlacementType $placementType,
        public string $message,
        public array $privateProfile,
        public bool $termsAccepted,
        public bool $privacyAccepted,
        public bool $referenceContactConsent,
    ) {}

    /** @param array<string, mixed> $validated */
    public static function fromValidated(array $validated): self
    {
        return new self(
            placementType: AdoptionPlacementType::from((string) $validated['placement_type']),
            message: trim((string) $validated['message']),
            privateProfile: [
                'experience' => trim((string) $validated['experience']),
                'home_context' => trim((string) $validated['home_context']),
                'household' => trim((string) $validated['household']),
                'other_animals' => trim((string) ($validated['other_animals'] ?? '')),
                'care_plan' => trim((string) $validated['care_plan']),
                'placement_reason' => trim((string) $validated['placement_reason']),
                'transport_plan' => trim((string) $validated['transport_plan']),
            ],
            termsAccepted: (bool) $validated['terms_accepted'],
            privacyAccepted: (bool) $validated['privacy_accepted'],
            referenceContactConsent: (bool) $validated['reference_contact_consent'],
        );
    }
}
