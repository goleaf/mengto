<x-app-shell :owner="$owner" :title="$page_title">
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
</x-app-shell>
