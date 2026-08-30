<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Enums\OnboardingPetChoice;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class OnboardingPetChoiceForm extends Form
{
    public string $choice = '';

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'choice' => ['required', Rule::enum(OnboardingPetChoice::class)],
        ];
    }

    /** @return array<string, string> */
    protected function messages(): array
    {
        return [
            'choice.required' => __('onboarding.validation.pet_choice'),
            'choice.enum' => __('onboarding.validation.pet_choice'),
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return [
            'choice' => __('onboarding.steps.pet_relationship.legend'),
        ];
    }

    public function selectedChoice(): OnboardingPetChoice
    {
        /** @var array{choice: string} $validated */
        $validated = $this->validate();

        return OnboardingPetChoice::from($validated['choice']);
    }
}
