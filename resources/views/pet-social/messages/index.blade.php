<x-pet-social.app-shell :owner="$owner" title="Messages | PawCircle" active-section="messages">
    <x-pet-social.page-stack>
        <x-pet-social.page-header
            :eyebrow="$summary['eyebrow']"
            :title="$summary['title']"
            :description="$summary['description']"
            :count="$summary['count']"
            action-label="New message"
            action-icon="mail"
            data-section="message-header"
        />

        <x-pet-social.message-center-layout>
            <x-slot:conversations>
            <x-pet-social.conversation-list :conversations="$conversations" :filters="$filters" :unread-count="$summary['unread_count']" />
            </x-slot:conversations>

            <x-slot:thread>
                <x-pet-social.message-thread :thread="$thread" />
            </x-slot:thread>
        </x-pet-social.message-center-layout>
    </x-pet-social.page-stack>
</x-pet-social.app-shell>
