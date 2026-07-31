<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Enums\SocialFollowPolicy;
use App\Enums\SocialFriendRequestPolicy;
use App\Enums\SocialListVisibility;
use App\Models\SocialActorSetting;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class SocialActorSettingsForm extends Form
{
    public string $friendRequestPolicy = 'everyone';

    public string $followPolicy = 'public';

    public string $friendListVisibility = 'friends';

    public string $followerListVisibility = 'count-only';

    public bool $isRecommendable = true;

    public bool $allowMessageRequests = true;

    public int $lockVersion = 1;

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'friendRequestPolicy' => [
                'required',
                Rule::in(array_map(
                    static fn (SocialFriendRequestPolicy $policy): string => $policy->value,
                    SocialFriendRequestPolicy::enforceableCases(),
                )),
            ],
            'followPolicy' => ['required', Rule::enum(SocialFollowPolicy::class)],
            'friendListVisibility' => ['required', Rule::enum(SocialListVisibility::class)],
            'followerListVisibility' => ['required', Rule::enum(SocialListVisibility::class)],
            'isRecommendable' => ['boolean'],
            'allowMessageRequests' => ['boolean'],
            'lockVersion' => ['required', 'integer', 'min:1'],
        ];
    }

    public function fillFrom(SocialActorSetting $settings): void
    {
        $this->friendRequestPolicy = $settings->friend_request_policy->value;
        $this->followPolicy = $settings->follow_policy->value;
        $this->friendListVisibility = $settings->friend_list_visibility->value;
        $this->followerListVisibility = $settings->follower_list_visibility->value;
        $this->isRecommendable = $settings->is_recommendable;
        $this->allowMessageRequests = $settings->allow_message_requests;
        $this->lockVersion = $settings->lock_version;
    }
}
