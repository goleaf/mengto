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
                'eyebrow' => __('messages.conversation_details_28b55e1258'),
                'title' => $conversation['name'],
                'description' => $neighbor['status'],
                'image' => $neighbor['image'],
                'image_small' => $neighbor['image_small'],
                'image_medium' => $neighbor['image_medium'],
                'image_alt' => $neighbor['image_alt'],
                'status_label' => $callRequested ? __('messages.call_requested_36c1ca13ef') : __('messages.messages_only_df579a7f2e'),
                'status_icon' => $callRequested ? 'phone-call' : 'message-circle',
                'status_tone' => $callRequested ? 'mint' : 'surface',
            ],
            'call' => [
                'requested' => $callRequested,
                'title' => $callRequested ? __('messages.waiting_for_mutual_consent_168fedcdbe') : __('messages.ask_before_calling_bd5bb392de'),
                'description' => $callRequested
                    ? __('presentation.waiting_for_acceptance', ['name' => $conversation['name']])
                    : __('messages.send_a_lightweight_request_first_voice_calling_stays_una_2bf77746d6'),
                'label' => $callRequested ? __('messages.cancel_request_5619668359') : __('messages.request_a_call_1af54145b2'),
                'icon' => $callRequested ? 'phone-off' : 'phone-call',
            ],
            'details' => [
                ['label' => __('messages.companion_1dadb3284b'), 'value' => $neighbor['pet']],
                ['label' => __('messages.neighborhood_1e99f12669'), 'value' => $neighbor['neighborhood']],
                ['label' => __('messages.distance_b7bdf7a2d6'), 'value' => $neighbor['distance']],
                ['label' => __('messages.shared_interests_c118d2e5eb'), 'value' => implode(', ', $neighbor['interests'])],
                [
                    'label' => __('messages.conversation_ccca181757'),
                    'value' => trans_choice('presentation.messages_count', count($messages), [
                        'count' => count($messages),
                    ]),
                ],
                [
                    'label' => __('messages.active_plans_98294ed57e'),
                    'value' => trans_choice('presentation.walk_plans_count', count($plans), [
                        'count' => count($plans),
                    ]),
                ],
            ],
            'safety' => [
                [
                    'icon' => 'user-round-check',
                    'title' => __('messages.confirm_who_is_joining_70fe9a3402'),
                    'description' => __('messages.keep_first_calls_and_meetups_tied_to_the_neighbor_and_pe_b812188ebb'),
                ],
                [
                    'icon' => 'lock-keyhole',
                    'title' => __('messages.keep_private_details_private_045cbfc7e3'),
                    'description' => __('messages.share_phone_numbers_home_addresses_and_access_details_on_139828e510'),
                ],
                [
                    'icon' => 'shield-check',
                    'title' => __('messages.stay_in_control_7bf8a4835c'),
                    'description' => __('messages.cancel_a_call_request_at_any_time_and_continue_the_conve_696e66fb95'),
                ],
            ],
            'plans' => $plans,
        ];
    }
}
