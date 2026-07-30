<x-layout.detail-page
    :owner="$owner"
    title="{{ $contact['title'] }} conversation | PawCircle"
    active-section="messages"
    section="conversation-details"
    :back-href="route('pet-social.messages.index', ['conversation' => $contact['key']])"
    back-label="Back to conversation"
>
    <x-slot:hero>
        <x-object.context-hero :context="$contact" section="conversation-context">
            <x-slot:actions>
                <x-ui.action-control
                    :href="route('pet-social.messages.index', ['conversation' => $contact['key']])"
                    label="Open messages"
                    icon="message-circle"
                    variant="paper"
                    size="regular"
                />
            </x-slot:actions>
        </x-object.context-hero>
    </x-slot:hero>

    <x-slot:main>
        <x-feature.call-consent-panel :call="$call" :contact="$contact" />

        <x-feature.walk-message-summary
            :plans="$plans"
            title="Plans with {{ $contact['title'] }}"
            title-id="conversation-walk-plans-title"
            empty-title="No active plans together"
            empty-description="Start a shared walk plan when you are ready to choose a time and meeting point."
        />

        <x-ui.content-panel
            section="conversation-safety"
            eyebrow="Before you connect"
            title="A comfortable next step"
        >
            <x-ui.icon-list :items="$safety" class="section-body" />
        </x-ui.content-panel>
    </x-slot:main>

    <x-slot:sidebar>
        <x-ui.content-panel section="conversation-summary" title="Connection summary">
            <x-ui.definition-list :items="$details" strong class="section-body" />
        </x-ui.content-panel>

        <x-ui.notice
            section="conversation-control"
            icon="lock-keyhole"
            title="Consent stays reversible"
            description="A call request can be cancelled at any time. Messages remain the default until both neighbors agree."
        />
    </x-slot:sidebar>
</x-layout.detail-page>
