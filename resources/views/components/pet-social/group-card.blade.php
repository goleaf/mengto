@props(['group', 'eager' => false])

<x-pet-social.directory-card data-group-card {{ $attributes }}>
    <x-slot:media>
        <x-pet-social.card-media
            :src="$group['image']"
            :small="$group['image_small'] ?? null"
            :medium="$group['image_medium'] ?? null"
            :alt="$group['image_alt']"
            :width="1200"
            :height="800"
            sizes="(min-width: 1280px) 292px, (min-width: 640px) calc(50vw - 2rem), calc(100vw - 2rem)"
            :eager="$eager"
        >
            <x-pet-social.status-badge :label="$group['category']" class="absolute left-3 top-3" />
        </x-pet-social.card-media>
    </x-slot:media>

    <p class="text-xs font-semibold text-paw-coral">{{ $group['topic'] }}</p>
    <h3 class="mt-2 break-words text-lg font-semibold text-paw-ink">{{ $group['name'] }}</h3>
    <p class="mt-2 text-sm leading-6 text-paw-muted">{{ $group['description'] }}</p>

    <x-pet-social.tag-list :items="$group['tags']" empty="Open to new neighbors." reserve class="mt-4" />

    <dl class="mt-5 grid grid-cols-2 gap-3 border-t border-paw-line pt-4">
        <div class="min-w-0">
            <dt>
                <x-pet-social.icon-text icon="users" class="text-[0.65rem] uppercase">
                    Community
                </x-pet-social.icon-text>
            </dt>
            <dd class="mt-1 text-sm font-semibold text-paw-ink">{{ $group['members'] }}</dd>
        </div>
        <div class="min-w-0">
            <dt>
                <x-pet-social.icon-text icon="activity" class="text-[0.65rem] uppercase">
                    Activity
                </x-pet-social.icon-text>
            </dt>
            <dd class="mt-1 text-sm font-semibold text-paw-ink">{{ $group['activity'] }}</dd>
        </div>
    </dl>

    <div class="mt-auto flex items-center gap-3 pt-5">
        <x-pet-social.initials-avatar :initials="$group['organizer_initials']" />
        <div class="min-w-0">
            <p class="truncate text-xs font-semibold text-paw-ink">Led by {{ $group['organizer'] }}</p>
            <p class="mt-0.5 text-xs text-paw-muted">Local organizer</p>
        </div>
        <x-pet-social.static-action label="Follow" icon="user-plus" variant="paper" class="ml-auto shrink-0" />
    </div>
</x-pet-social.directory-card>
