@props([
    'person',
    'kicker',
    'actionLabel' => null,
    'actionIcon' => null,
])

<div {{ $attributes->class(['pc-person-summary']) }}>
    <p class="pc-person-summary__kicker">{{ $kicker }}</p>

    <div class="pc-person-summary__identity">
        <x-pet-social.initials-avatar
            :initials="$person['initials']"
            :tone="$person['tone']"
            size="regular"
        />
        <div class="pc-person-summary__content">
            <h2 class="pc-person-summary__name">{{ $person['name'] }}</h2>
            <p class="pc-person-summary__detail">{{ $person['role'] }}</p>
        </div>
    </div>

    <p class="pc-person-summary__bio">{{ $person['bio'] }}</p>

    @if ($actionLabel)
        <x-pet-social.static-action
            :label="$actionLabel"
            :icon="$actionIcon"
            variant="paper"
            size="regular"
            class="pc-person-summary__action"
        />
    @endif
</div>
