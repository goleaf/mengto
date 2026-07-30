@props(['pet', 'eager' => false])

<article role="listitem" data-profile-pet class="panel panel--clip grid sm:grid-cols-[10rem_minmax(0,1fr)]">
    <x-responsive-image
        :src="$pet['image']"
        :small="$pet['image_small'] ?? null"
        :medium="$pet['image_medium'] ?? null"
        :alt="$pet['image_alt']"
        :width="1200"
        :height="900"
        sizes="(min-width: 640px) 160px, calc(100vw - 2rem)"
        :eager="$eager"
        class="aspect-[4/3] w-full object-cover sm:aspect-auto sm:h-full"
    />

    <div class="min-w-0 p-4 sm:p-5">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <x-pet-badge :type="$pet['species']" tone="mint" />
                <h3 class="mt-1 break-words text-lg font-semibold text-paw-ink">
                    <x-optional-link
                        :route-name="$pet['profile_route'] ?? null"
                        :route-parameters="$pet['profile_parameters'] ?? []"
                    >
                        {{ $pet['name'] }}
                    </x-optional-link>
                </h3>
            </div>
            <span class="shrink-0 text-xs font-semibold text-paw-muted">{{ $pet['age'] }}</span>
        </div>

        <p class="mt-1 text-sm text-paw-muted">{{ $pet['breed'] }}</p>
        <p class="mt-3 text-sm font-medium text-paw-ink">{{ $pet['status'] }}</p>

        <x-tag-list :items="$pet['traits']" empty="No traits shared." class="mt-3" />
    </div>
</article>
