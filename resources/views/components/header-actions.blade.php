@props(['owner' => null, 'activeSection'])

<div {{ $attributes->class('header-actions') }}>
    @if ($owner !== null)
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
        href="{{ $owner['profile_url'] }}"
        aria-label="{{ __('navigation.utility.profile_for', ['name' => $owner['name']]) }}"
        title="{{ __('navigation.utility.profile_for', ['name' => $owner['name']]) }}"
        data-header-link="profile"
        @if ($activeSection === 'profile') aria-current="page" @endif
        class="header-profile rounded-full focus:outline-none focus-visible:ring-2 focus-visible:ring-paw-leaf focus-visible:ring-offset-2"
    >
        @if ($owner['avatar'] !== null)
            <x-avatar
                :src="$owner['avatar']"
                size="header"
                decorative
                :class="$activeSection === 'profile' ? 'ring-2 ring-paw-ink' : 'ring-2 ring-white'"
            />
        @else
            <x-initials-avatar
                :initials="$owner['initials']"
                tone="mint"
                size="regular"
                :class="$activeSection === 'profile' ? 'ring-2 ring-paw-ink' : 'ring-2 ring-white'"
            />
        @endif
        <span class="header-profile__name">{{ $owner['name'] }}</span>
    </a>
    @else
        <x-action-control
            :href="route('login')"
            :label="__('navigation.utility.sign_in')"
            icon="log-in"
            variant="quiet"
        />
        <x-action-control
            :href="route('register')"
            :label="__('navigation.utility.create_account')"
            icon="user-plus"
            variant="primary"
        />
    @endif
</div>
