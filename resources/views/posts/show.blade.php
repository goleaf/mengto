<x-app-shell
    :owner="$owner"
    :title="'Conversation about '.$post['pet'].' | PawCircle'"
    active-section="feed"
>
    <x-page-stack>
        <x-page-header
            eyebrow="Neighborhood conversation"
            :title="$post['pet'].'’s moment'"
            description="Follow the local context, add a thoughtful reply, or return to the neighborhood feed."
        >
            <x-slot:actions>
                <x-action-control
                    label="Back to feed"
                    icon="arrow-left"
                    :href="$post['return_url'] ?? route('home')"
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
                    eyebrow="Community notes"
                    title="Keep replies useful"
                    size="compact"
                >
                    <x-icon-list :items="$threadGuide" class="mt-4" />
                </x-content-panel>
            </x-slot:sidebar>
        </x-main-sidebar-layout>
    </x-page-stack>
</x-app-shell>
