<x-layout.detail-page
    :owner="$owner"
    title="Share {{ $share['item']['title'] }} | PawCircle"
    :active-section="$share['item']['active_section']"
    section="share-hub"
    :back-href="$share['item']['url']"
    back-label="Back to original"
>
    <x-slot:hero>
        <x-object.context-hero :context="$share['item']" section="share-context">
            <x-slot:actions>
                <x-ui.action-control
                    :href="$share['item']['url']"
                    label="Open original"
                    icon="external-link"
                    variant="paper"
                    size="regular"
                />
            </x-slot:actions>
        </x-object.context-hero>
    </x-slot:hero>

    <x-slot:main>
        <x-ui.content-panel
            section="share-channels"
            eyebrow="Outside PawCircle"
            title="Choose a channel"
            :meta="count($share['channels']).' options'"
        >
            <x-feature.share-channel-grid
                :channels="$share['channels']"
                class="section-body"
            />
        </x-ui.content-panel>

        <x-ui.content-panel
            section="share-neighbors"
            eyebrow="Inside PawCircle"
            title="Send to a neighbor"
            :meta="count($share['recipients']).' neighbors'"
        >
            <x-feature.share-recipient-list
                :recipients="$share['recipients']"
                class="section-body"
            />
        </x-ui.content-panel>
    </x-slot:main>

    <x-slot:sidebar>
        <x-ui.content-panel section="share-link-details" title="Share details">
            <x-ui.definition-list
                :items="$share['linkDetails']"
                strong
                class="section-body"
            />
        </x-ui.content-panel>

        <x-ui.notice
            section="share-privacy"
            icon="shield-check"
            title="You choose the audience"
            description="The link opens public PawCircle content. Private messages and contact details are never included."
        />
    </x-slot:sidebar>
</x-layout.detail-page>
