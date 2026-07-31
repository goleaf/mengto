<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\AdoptionApplicationData;
use App\Enums\AdoptionPlacementType;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class AdoptionApplicationForm extends Form
{
    public string $placementType = 'adoption';

    public string $message = '';

    public string $experience = '';

    public string $homeContext = '';

    public string $household = '';

    public string $otherAnimals = '';

    public string $carePlan = '';

    public string $placementReason = '';

    public string $transportPlan = '';

    public bool $termsAccepted = false;

    public bool $privacyAccepted = false;

    public bool $referenceContactConsent = false;

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'placementType' => ['required', Rule::enum(AdoptionPlacementType::class)],
            'message' => ['required', 'string', 'min:20', 'max:1500'],
            'experience' => ['required', 'string', 'min:20', 'max:1500'],
            'homeContext' => ['required', 'string', 'min:20', 'max:1500'],
            'household' => ['required', 'string', 'min:10', 'max:1000'],
            'otherAnimals' => ['nullable', 'string', 'max:1000'],
            'carePlan' => ['required', 'string', 'min:20', 'max:1500'],
            'placementReason' => ['required', 'string', 'min:20', 'max:1500'],
            'transportPlan' => ['required', 'string', 'min:10', 'max:1000'],
            'termsAccepted' => ['accepted'],
            'privacyAccepted' => ['accepted'],
            'referenceContactConsent' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return [
            'placementType' => __('adoption.fields.placement_type'),
            'message' => __('adoption.fields.message'),
            'experience' => __('adoption.fields.experience'),
            'homeContext' => __('adoption.fields.home_context'),
            'household' => __('adoption.fields.household'),
            'otherAnimals' => __('adoption.fields.other_animals'),
            'carePlan' => __('adoption.fields.care_plan'),
            'placementReason' => __('adoption.fields.placement_reason'),
            'transportPlan' => __('adoption.fields.transport_plan'),
            'termsAccepted' => __('adoption.fields.terms_accepted'),
            'privacyAccepted' => __('adoption.fields.privacy_accepted'),
            'referenceContactConsent' => __('adoption.fields.reference_consent'),
        ];
    }

    public function data(): AdoptionApplicationData
    {
        $validated = $this->validate();

        return AdoptionApplicationData::fromValidated([
            'placement_type' => $validated['placementType'],
            'message' => $validated['message'],
            'experience' => $validated['experience'],
            'home_context' => $validated['homeContext'],
            'household' => $validated['household'],
            'other_animals' => $validated['otherAnimals'],
            'care_plan' => $validated['carePlan'],
            'placement_reason' => $validated['placementReason'],
            'transport_plan' => $validated['transportPlan'],
            'terms_accepted' => $validated['termsAccepted'],
            'privacy_accepted' => $validated['privacyAccepted'],
            'reference_contact_consent' => $validated['referenceContactConsent'],
        ]);
    }
}
