<x-detail-page
    :owner="$owner"
    :title="__('presentation.share_title', ['title' => $share['item']['title']])"
    :active-section="$share['item']['active_section']"
    section="share-hub"
    :back-href="$share['item']['url']"
    back-label="{{ __('ui.back_to_original_de5c83c1ad') }}"
>
    <x-slot:hero>
        <x-context-hero :context="$share['item']" section="share-context">
            <x-slot:actions>
                <x-action-control
                    :href="$share['item']['url']"
                    label="{{ __('ui.open_original_44a915faf3') }}"
                    icon="external-link"
                    variant="paper"
                    size="regular"
                />
            </x-slot:actions>
        </x-context-hero>
    </x-slot:hero>

    <x-slot:main>
        <x-content-panel
            section="share-channels"
            eyebrow="{{ __('ui.outside_brand_23da29dae7') }}"
            title="{{ __('ui.choose_a_channel_863068958b') }}"
            :meta="trans_choice('presentation.options_count', count($share['channels']), ['count' => count($share['channels'])])"
        >
            <x-share-channel-grid
                :channels="$share['channels']"
                class="section-body"
            />
        </x-content-panel>

        <x-content-panel
            section="share-neighbors"
            eyebrow="{{ __('ui.inside_brand_1489459397') }}"
            title="{{ __('ui.send_to_a_neighbor_78ef3e2392') }}"
            :meta="trans_choice('presentation.neighbors_count', count($share['recipients']), ['count' => count($share['recipients'])])"
        >
            <x-share-recipient-list
                :recipients="$share['recipients']"
                class="section-body"
            />
        </x-content-panel>
    </x-slot:main>

    <x-slot:sidebar>
        <x-content-panel section="share-link-details" title="{{ __('ui.share_details_ffe7389c12') }}">
            <x-definition-list
                :items="$share['linkDetails']"
                strong
                class="section-body"
            />
        </x-content-panel>

        <x-notice
            section="share-privacy"
            icon="shield-check"
            title="{{ __('ui.you_choose_the_audience_8aa845be9d') }}"
            description="{{ __('ui.the_link_opens_public_brand_content_private_messages_2ad670ee20') }}"
        />
    </x-slot:sidebar>
</x-detail-page>
