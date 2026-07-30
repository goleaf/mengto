@props([
    'owner',
    'avatarSize' => 'regular',
    'variant' => 'default',
    'routeName' => null,
    'routeParameters' => [],
])

<div {{ $attributes->class([
    'flex',
    'items-center gap-3' => $variant === 'default',
    'items-start gap-4' => $variant === 'profile',
]) }}>
    <x-avatar
        :src="$owner['avatar']"
        :alt="$owner['name']"
        :size="$avatarSize"
    />

    <div class="min-w-0">
        <h2 class="text-base font-semibold text-paw-ink">
            <x-optional-link
                :route-name="$routeName"
                :route-parameters="$routeParameters"
            >
                {{ $owner['name'] }}
            </x-optional-link>
        </h2>
        <x-icon-text icon="map-pin" class="mt-1">{{ $owner['location'] }}</x-icon-text>
    </div>
</div>
