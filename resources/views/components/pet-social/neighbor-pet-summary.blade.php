@props(['pet'])

<section data-section="neighbor-pet" {{ $attributes->merge(['class' => 'pc-panel pc-panel--clip']) }}>
    <div class="grid sm:grid-cols-[14rem_minmax(0,1fr)]">
        <x-pet-social.responsive-image
            :src="$pet['image']"
            :small="$pet['image_small'] ?? null"
            :medium="$pet['image_medium'] ?? null"
            :alt="$pet['image_alt']"
            :width="1200"
            :height="900"
            sizes="(min-width: 640px) 224px, calc(100vw - 2rem)"
            class="aspect-[4/3] h-full w-full object-cover sm:aspect-auto"
        />

        <div class="p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-normal text-paw-leaf">Lives with {{ $pet['owner_name'] }}</p>
                    <h2 class="mt-2 break-words text-xl font-semibold text-paw-ink">{{ $pet['name'] }}</h2>
                    <p class="mt-1 text-sm font-semibold text-paw-coral">{{ $pet['breed'] }} · {{ $pet['age'] }}</p>
                </div>
                <x-pet-social.static-action label="Plan a walk" icon="footprints" variant="paper" class="shrink-0" />
            </div>

            <p class="mt-3 text-sm leading-6 text-paw-muted">{{ $pet['status'] }}</p>

            <x-pet-social.tag-list :items="$pet['traits']" empty="Routine notes unavailable." class="mt-4" />

            <dl class="mt-5 grid gap-3 border-t border-paw-line pt-4 sm:grid-cols-3">
                @forelse ($pet['routine'] as $item)
                    <div>
                        <dt class="text-[0.65rem] font-semibold uppercase text-paw-muted">{{ $item['label'] }}</dt>
                        <dd class="mt-1 text-sm font-semibold leading-5 text-paw-ink">{{ $item['value'] }}</dd>
                    </div>
                @empty
                    <div class="text-sm text-paw-muted">No routine details yet.</div>
                @endforelse
            </dl>
        </div>
    </div>
</section>
