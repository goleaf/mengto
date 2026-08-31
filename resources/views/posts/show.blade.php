<x-app-shell
    :title="__('presentation.brand_title', ['title' => __('ui.conversation_about').' '.$post['pet']])"
    active-section="feed"
>
    <x-page-stack>
        <x-page-header
            :eyebrow="__('ui.neighborhood_conversation')"
            :title="__('presentation.pet_moment_title', ['pet' => $post['pet']])"
            :description="__('ui.follow_the_local_context_add_a_thoughtful_reply_or_return_to_the_neighborhood_feed')"
            heading-id="post-thread-heading"
        >
            <x-slot:actions>
                <x-action-control
                    :label="__('ui.back_to_feed')"
                    icon="arrow-left"
                    :href="$post['return_url'] ?? route('preview.feed')"
                    variant="paper"
                />
            </x-slot:actions>
        </x-page-header>

        <x-main-sidebar-layout variant="compact">
            <x-slot:main>
                <x-page-stack gap="section">
                    <x-feed-card :post="$post" eager />
                    <x-comment-thread
                        :post="$post"
                        :comments="$comments"
                        :count="$commentCount"
                    />
                </x-page-stack>
            </x-slot:main>

            <x-slot:sidebar>
                <x-owner-summary :owner="$owner" />

                <x-content-panel
                    section="thread-guide"
                    eyebrow="{{ __('ui.community_notes') }}"
                    title="{{ __('ui.keep_replies_useful') }}"
                    size="compact"
                >
                    <x-icon-list :items="$threadGuide" class="mt-4" />
                </x-content-panel>
            </x-slot:sidebar>
        </x-main-sidebar-layout>
    </x-page-stack>
</x-app-shell>
