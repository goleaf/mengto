@props(['profile', 'eyebrow', 'avatarAlt'])

<img
    src="{{ $profile['avatar'] }}"
    alt="{{ $avatarAlt }}"
    width="112"
    height="112"
    class="pc-profile-hero__avatar"
>

<div class="pc-profile-hero__identity">
    <p class="pc-profile-hero__eyebrow">{{ $eyebrow }}</p>
    <h1 class="pc-profile-hero__name">{{ $profile['name'] }}</h1>
    <p class="pc-profile-hero__meta">{{ $profile['location'] }} · {{ $profile['member_since'] }}</p>
</div>
