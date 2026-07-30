<x-detail-page
    :owner="$owner"
    :title="__('presentation.conversation_title', ['name' => $contact['title']])"
    active-section="messages"
    section="conversation-details"
    :back-href="route('messages.index', ['conversation' => $contact['key']])"
    back-label="{{ __('ui.back_to_conversation_74ad9c3261') }}"
>
    <x-slot:hero>
        <x-context-hero :context="$contact" section="conversation-context">
            <x-slot:actions>
                <x-action-control
                    :href="route('messages.index', ['conversation' => $contact['key']])"
                    label="{{ __('ui.open_messages_cf997592c9') }}"
                    icon="message-circle"
                    variant="paper"
                    size="regular"
                />
            </x-slot:actions>
        </x-context-hero>
    </x-slot:hero>

    <x-slot:main>
        <x-call-consent-panel :call="$call" :contact="$contact" />

        <x-walk-message-summary
            :plans="$plans"
            :title="__('presentation.plans_with', ['name' => $contact['title']])"
            title-id="conversation-walk-plans-title"
            empty-title="{{ __('ui.no_active_plans_together_2aaa4607bd') }}"
            empty-description="{{ __('ui.start_a_shared_walk_plan_when_you_are_757ddeec63') }}"
        />

        <x-content-panel
            section="conversation-safety"
            eyebrow="{{ __('ui.before_you_connect_5ff346291c') }}"
            title="{{ __('ui.a_comfortable_next_step_083cf6b3d6') }}"
        >
            <x-icon-list :items="$safety" class="section-body" />
        </x-content-panel>
    </x-slot:main>

    <x-slot:sidebar>
        <x-content-panel section="conversation-summary" title="{{ __('ui.connection_summary_de5459f742') }}">
            <x-definition-list :items="$details" strong class="section-body" />
        </x-content-panel>

        <x-notice
            section="conversation-control"
            icon="lock-keyhole"
            title="{{ __('ui.consent_stays_reversible_b1ac7fa389') }}"
            description="{{ __('ui.a_call_request_can_be_cancelled_at_any_9332f43614') }}"
        />
    </x-slot:sidebar>
</x-detail-page>
