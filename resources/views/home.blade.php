<x-layout.app-shell :owner="$owner" :title="$page_title">
    <x-layout.feed-page-layout>
        <x-slot:feed>
            <x-feature.feed-stream :feed="$feed" />
        </x-slot:feed>

        <x-slot:profile>
            <x-object.profile-card :owner="$owner" :pets="$pets" />
        </x-slot:profile>

        <x-slot:sidebar>
            <x-feature.nearby-meetup-list :meetups="$meetups" />
            <x-feature.group-suggestion-list :groups="$groups" />
            <x-feature.care-tip-list :tips="$tips" />
        </x-slot:sidebar>
    </x-layout.feed-page-layout>
</x-layout.app-shell>
