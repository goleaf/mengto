<x-pet-social.app-shell :owner="$owner">
    <x-pet-social.feed-page-layout>
        <x-slot:feed>
            <x-pet-social.feed-stream :posts="$posts" />
        </x-slot:feed>

        <x-slot:profile>
            <x-pet-social.profile-card :owner="$owner" :pets="$pets" />
        </x-slot:profile>

        <x-slot:sidebar>
            <x-pet-social.nearby-meetup-list :meetups="$meetups" />
            <x-pet-social.group-suggestion-list :groups="$groups" />
            <x-pet-social.care-tip-list :tips="$tips" />
        </x-slot:sidebar>
    </x-pet-social.feed-page-layout>
</x-pet-social.app-shell>
