@props([
    'owner',
    'avatarSize' => 'regular',
    'variant' => 'default',
    'href' => null,
    'linkLabel' => null,
])

<div {{ $attributes->class([
    'flex',
    'items-center gap-3' => $variant === 'default',
    'items-start gap-4' => $variant === 'profile',
]) }}>
    <x-linked-media
        :href="$href"
        :label="$linkLabel"
        variant="avatar"
        class="shrink-0"
    >
        <x-avatar
            :src="$owner['avatar']"
            :alt="$owner['name']"
            :size="$avatarSize"
        />
    </x-linked-media>

    <div class="min-w-0">
        <h2 class="text-base font-semibold text-paw-ink">
            <x-optional-link
                :href="$href"
            >
                {{ $owner['name'] }}
            </x-optional-link>
        </h2>
        <x-icon-text icon="map-pin" class="mt-1">{{ $owner['location'] }}</x-icon-text>
    </div>
</div>
