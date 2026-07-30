<?php

namespace App\Services;

final class ConversationPresenter
{
    public function __construct(
        private readonly PrototypeState $state,
        private readonly WalkPlanPresenter $walks,
    ) {}

    /**
     * @param  array<string, mixed>  $owner
     * @param  array<string, mixed>  $neighbor
     * @param  array<string, mixed>  $conversation
     * @param  array<int, array<string, mixed>>  $messages
     * @return array<string, mixed>
     */
    public function present(array $owner, array $neighbor, array $conversation, array $messages): array
    {
        $callRequested = $this->state->isActive('call-requests', $conversation['key']);
        $plans = $this->walks->plansForConversation($conversation['key']);

        return [
            'owner' => $owner,
            'contact' => [
                'key' => $conversation['key'],
                'eyebrow' => 'Conversation details',
                'title' => $conversation['name'],
                'description' => $neighbor['status'],
                'image' => $neighbor['image'],
                'image_small' => $neighbor['image_small'],
                'image_medium' => $neighbor['image_medium'],
                'image_alt' => $neighbor['image_alt'],
                'status_label' => $callRequested ? 'Call requested' : 'Messages only',
                'status_icon' => $callRequested ? 'phone-call' : 'message-circle',
                'status_tone' => $callRequested ? 'mint' : 'surface',
            ],
            'call' => [
                'requested' => $callRequested,
                'title' => $callRequested ? 'Waiting for mutual consent' : 'Ask before calling',
                'description' => $callRequested
                    ? $conversation['name'].' can accept from their side. Messaging stays available while you wait.'
                    : 'Send a lightweight request first. Voice calling stays unavailable until both neighbors agree.',
                'label' => $callRequested ? 'Cancel request' : 'Request a call',
                'icon' => $callRequested ? 'phone-off' : 'phone-call',
            ],
            'details' => [
                ['label' => 'Companion', 'value' => $neighbor['pet']],
                ['label' => 'Neighborhood', 'value' => $neighbor['neighborhood']],
                ['label' => 'Distance', 'value' => $neighbor['distance']],
                ['label' => 'Shared interests', 'value' => implode(', ', $neighbor['interests'])],
                ['label' => 'Conversation', 'value' => count($messages).' messages'],
                ['label' => 'Active plans', 'value' => count($plans).' walk '.($plans === [] || count($plans) !== 1 ? 'plans' : 'plan')],
            ],
            'safety' => [
                [
                    'icon' => 'user-round-check',
                    'title' => 'Confirm who is joining',
                    'description' => 'Keep first calls and meetups tied to the neighbor and pet already shown in this conversation.',
                ],
                [
                    'icon' => 'lock-keyhole',
                    'title' => 'Keep private details private',
                    'description' => 'Share phone numbers, home addresses, and access details only when both sides are comfortable.',
                ],
                [
                    'icon' => 'shield-check',
                    'title' => 'Stay in control',
                    'description' => 'Cancel a call request at any time and continue the conversation by message.',
                ],
            ],
            'plans' => $plans,
        ];
    }
}
