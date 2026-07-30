@props(['owner', 'activeSection'])

<div class="ml-auto flex items-center gap-2">
    <x-icon-link
        :href="route('circle.index')"
        label="{{ __('ui.my_circle_201c8528b5') }}"
        icon="bookmark"
        name="circle"
        :active="$activeSection === 'circle'"
        class="header-icon--from-sm"
    />
    <x-icon-link
        :href="route('notifications.index')"
        label="{{ __('ui.notifications_788011833a') }}"
        icon="bell"
        name="notifications"
        :active="$activeSection === 'notifications'"
    />
    <x-icon-link
        :href="route('messages.index')"
        label="{{ __('ui.messages_04d7b48339') }}"
        icon="mail"
        name="messages"
        :active="$activeSection === 'messages'"
    />
    <a
        href="{{ route('profile.mia') }}"
        aria-label="{{ __('presentation.profile_for', ['name' => $owner['name']]) }}"
        title="{{ __('presentation.profile_for', ['name' => $owner['name']]) }}"
        data-header-link="profile"
        @if ($activeSection === 'profile') aria-current="page" @endif
        class="header-profile rounded-full focus:outline-none focus-visible:ring-2 focus-visible:ring-paw-leaf focus-visible:ring-offset-2"
    >
        <x-avatar
            :src="$owner['avatar']"
            size="header"
            decorative
            :class="$activeSection === 'profile' ? 'ring-2 ring-paw-ink' : 'ring-2 ring-white'"
        />
    </a>
</div>
