@props(['pet'])

<section {{ $attributes->merge(['class' => 'pc-panel pc-panel--clip']) }}>
    <x-pet-social.responsive-image
        :src="$pet['cover_image']"
        :small="$pet['cover_image_small'] ?? null"
        :medium="$pet['cover_image_medium'] ?? null"
        :alt="$pet['name'].' enjoying an outdoor walk'"
        :width="1600"
        :height="900"
        :small-width="720"
        :medium-width="1200"
        sizes="(min-width: 1280px) 768px, (min-width: 1024px) calc(67vw - 3rem), calc(100vw - 2rem)"
        :eager="true"
        class="aspect-[4/3] w-full object-cover sm:aspect-[16/7]"
    />

    <div class="px-5 pb-5 sm:px-6 sm:pb-6">
        <div class="-mt-12 flex flex-col items-start gap-4 sm:flex-row sm:items-end">
            <img
                src="{{ $pet['profile_image'] }}"
                alt="{{ $pet['name'] }}"
                width="96"
                height="96"
                class="size-24 shrink-0 rounded-full border-4 border-white object-cover shadow-sm"
            >

            <div class="min-w-0 sm:pb-1">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-paw-leaf">{{ $pet['species'] }}</p>
                <h1 class="mt-1 break-words text-3xl font-semibold text-paw-ink">{{ $pet['name'] }}</h1>
                <p class="mt-1 text-sm text-paw-muted">
                    {{ $pet['breed'] }} · {{ $pet['age'] }} · {{ $pet['location'] }}
                </p>
            </div>

            <x-pet-social.static-action
                label="Plan a walk"
                icon="footprints"
                variant="paper"
                size="profile"
                class="w-full sm:mb-1 sm:ml-auto sm:w-auto"
            />
        </div>

        <x-pet-social.status-badge :label="$pet['status']" icon="circle-check" tone="mint" size="regular" class="mt-4" />
    </div>
</section>
