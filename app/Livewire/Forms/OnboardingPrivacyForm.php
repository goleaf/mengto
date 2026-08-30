<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Models\SocialActor;
use App\Models\SocialActorSetting;
use Livewire\Form;

final class OnboardingPrivacyForm extends Form
{
    public bool $isDiscoverable = false;

    public bool $isRecommendable = false;

    public bool $allowMessageRequests = false;

    /** @return array<string, list<string>> */
    protected function rules(): array
    {
        return [
            'isDiscoverable' => ['required', 'boolean'],
            'isRecommendable' => ['required', 'boolean'],
            'allowMessageRequests' => ['required', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    protected function messages(): array
    {
        return [
            'isDiscoverable.boolean' => __('onboarding.validation.privacy_choice'),
            'isRecommendable.boolean' => __('onboarding.validation.privacy_choice'),
            'allowMessageRequests.boolean' => __('onboarding.validation.privacy_choice'),
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return [
            'isDiscoverable' => __('onboarding.steps.privacy_discovery.discoverable_label'),
            'isRecommendable' => __('onboarding.steps.privacy_discovery.recommendable_label'),
            'allowMessageRequests' => __('onboarding.steps.privacy_discovery.messages_label'),
        ];
    }

    public function fillFrom(SocialActor $actor, SocialActorSetting $settings): void
    {
        $this->isDiscoverable = $actor->is_discoverable;
        $this->isRecommendable = $settings->is_recommendable;
        $this->allowMessageRequests = $settings->allow_message_requests;
    }

    /**
     * @return array{isDiscoverable: bool, isRecommendable: bool, allowMessageRequests: bool}
     */
    public function validatedData(): array
    {
        /** @var array{isDiscoverable: bool, isRecommendable: bool, allowMessageRequests: bool} $validated */
        $validated = $this->validate();

        return $validated;
    }
}
