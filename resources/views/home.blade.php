<x-app-shell :owner="$owner" :title="$page_title">
    <div class="grid gap-5">
        <x-page-header
            :eyebrow="$feed['summary']['eyebrow']"
            :title="$feed['summary']['title']"
            :description="$feed['summary']['description']"
            heading-id="social-feed-heading"
            :count="$feed['summary']['count']"
            data-section="social-feed-header"
        >
            <x-slot:actions>
                @if ($feed['sort'] !== 'latest')
                    <x-action-control
                        :label="__('ui.new_posts_35b75561fc')"
                        icon="refresh-cw"
                        :href="$feed['new_posts_url']"
                        variant="paper"
                        size="regular"
                    />
                @endif
                <x-action-control
                    :label="__('ui.new_post_7e50e2667b')"
                    icon="plus"
                    :href="$feed['composer_url']"
                    size="regular"
                />
            </x-slot:actions>
        </x-page-header>

        <x-feed-page-layout>
            <x-slot:feed>
                <x-feed-stream :feed="$feed" />
            </x-slot:feed>

            <x-slot:profile>
                <x-profile-card :owner="$owner" :pets="$pets" />
            </x-slot:profile>

            <x-slot:sidebar>
                <x-nearby-meetup-list :meetups="$meetups" />
                <x-group-suggestion-list :groups="$groups" />
                <x-care-tip-list :tips="$tips" />
            </x-slot:sidebar>
        </x-feed-page-layout>
    </div>
</x-app-shell>
