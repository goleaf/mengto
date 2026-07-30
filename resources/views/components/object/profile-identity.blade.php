@props(['profile', 'eyebrow', 'avatarAlt'])

<img
    src="{{ $profile['avatar'] }}"
    alt="{{ $avatarAlt }}"
    width="112"
    height="112"
    decoding="async"
    class="profile-hero__avatar"
>

<div class="profile-hero__identity">
    <p class="profile-hero__eyebrow">{{ $eyebrow }}</p>
    <h1 class="profile-hero__name">{{ $profile['name'] }}</h1>
    @if ($profile['handle'] ?? null)
        <p class="profile-hero__handle">{{ $profile['handle'] }}</p>
    @endif
    <p class="profile-hero__meta">{{ $profile['location'] }} · {{ $profile['member_since'] }}</p>
</div>
