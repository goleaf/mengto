@props(['owner', 'activeSection'])

<div class="ml-auto flex items-center gap-2">
    <x-ui.icon-link
        :href="route('circle.index')"
        label="My circle"
        icon="bookmark"
        name="circle"
        :active="$activeSection === 'circle'"
        class="header-icon--from-sm"
    />
    <x-ui.icon-link
        :href="route('notifications.index')"
        label="Notifications"
        icon="bell"
        name="notifications"
        :active="$activeSection === 'notifications'"
    />
    <x-ui.icon-link
        :href="route('messages.index')"
        label="Messages"
        icon="mail"
        name="messages"
        :active="$activeSection === 'messages'"
    />
    <a
        href="{{ route('profile.mia') }}"
        aria-label="{{ $owner['name'] }} profile"
        title="{{ $owner['name'] }} profile"
        data-header-link="profile"
        @if ($activeSection === 'profile') aria-current="page" @endif
        class="header-profile rounded-full focus:outline-none focus-visible:ring-2 focus-visible:ring-paw-leaf focus-visible:ring-offset-2"
    >
        <x-ui.avatar
            :src="$owner['avatar']"
            size="header"
            decorative
            :class="$activeSection === 'profile' ? 'ring-2 ring-paw-ink' : 'ring-2 ring-white'"
        />
    </a>
</div>
