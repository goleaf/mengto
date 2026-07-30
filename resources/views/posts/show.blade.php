<x-layout.app-shell
    :owner="$owner"
    :title="'Conversation about '.$post['pet'].' | PawCircle'"
    active-section="feed"
>
    <x-layout.page-stack>
        <x-layout.page-header
            eyebrow="Neighborhood conversation"
            :title="$post['pet'].'’s moment'"
            description="Follow the local context, add a thoughtful reply, or return to the neighborhood feed."
        >
            <x-slot:actions>
                <x-ui.action-control
                    label="Back to feed"
                    icon="arrow-left"
                    :href="$post['return_url'] ?? route('home')"
                    variant="paper"
                />
            </x-slot:actions>
        </x-layout.page-header>

        <x-layout.main-sidebar-layout variant="compact">
            <x-slot:main>
                <x-layout.page-stack gap="section">
                    <x-feature.feed-card :post="$post" eager />
                    <x-feature.comment-thread
                        :post="$post"
                        :comments="$comments"
                        :count="$commentCount"
                    />
                </x-layout.page-stack>
            </x-slot:main>

            <x-slot:sidebar>
                <x-object.owner-summary :owner="$owner" />

                <x-ui.content-panel
                    section="thread-guide"
                    eyebrow="Community notes"
                    title="Keep replies useful"
                    size="compact"
                >
                    <x-ui.icon-list :items="$threadGuide" class="mt-4" />
                </x-ui.content-panel>
            </x-slot:sidebar>
        </x-layout.main-sidebar-layout>
    </x-layout.page-stack>
</x-layout.app-shell>
