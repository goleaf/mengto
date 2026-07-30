@props([
    'person',
    'kicker',
    'actionLabel' => null,
    'actionIcon' => null,
    'actionHref' => null,
])

<div {{ $attributes->class(['person-summary']) }}>
    <p class="person-summary__kicker">{{ $kicker }}</p>

    <div class="person-summary__identity">
        <x-ui.initials-avatar
            :initials="$person['initials']"
            :tone="$person['tone']"
            size="regular"
        />
        <div class="person-summary__content">
            <h2 class="person-summary__name">{{ $person['name'] }}</h2>
            <p class="person-summary__detail">{{ $person['role'] }}</p>
        </div>
    </div>

    <p class="person-summary__bio">{{ $person['bio'] }}</p>

    @if ($actionLabel)
        <x-ui.action-control
            :label="$actionLabel"
            :icon="$actionIcon"
            :href="$actionHref"
            variant="paper"
            size="regular"
            class="person-summary__action"
        />
    @endif
</div>
