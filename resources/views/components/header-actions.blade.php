@props(['owner', 'activeSection'])

<div {{ $attributes->class('header-actions') }}>
    <x-icon-link
        :href="route('circle.index')"
        label="{{ __('navigation.utility.circle') }}"
        icon="bookmark"
        name="circle"
        :active="$activeSection === 'circle'"
        class="header-icon--from-sm"
    />
    <x-icon-link
        :href="route('notifications.index')"
        label="{{ __('navigation.utility.notifications') }}"
        icon="bell"
        name="notifications"
        :active="$activeSection === 'notifications'"
    />
    <x-icon-link
        :href="route('messages.index')"
        label="{{ __('navigation.utility.messages') }}"
        icon="mail"
        name="messages"
        :active="$activeSection === 'messages'"
    />
    <a
        href="{{ route('profile.mia') }}"
        aria-label="{{ __('navigation.utility.profile_for', ['name' => $owner['name']]) }}"
        title="{{ __('navigation.utility.profile_for', ['name' => $owner['name']]) }}"
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
        <span class="header-profile__name">{{ $owner['name'] }}</span>
    </a>
</div>
