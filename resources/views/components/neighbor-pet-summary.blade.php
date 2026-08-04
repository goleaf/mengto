@props(['pet', 'copy'])

<section data-section="neighbor-pet" data-neighbor-profile-pet {{ $attributes->merge(['class' => 'panel panel--clip']) }}>
    <div class="grid sm:grid-cols-[14rem_minmax(0,1fr)]">
        <x-responsive-image
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
                    <p class="text-xs font-semibold uppercase tracking-normal text-paw-leaf" data-neighbor-profile-pet-owner>{{ $copy['lives_with'] }}</p>
                    <h2 class="mt-2 break-words text-xl font-semibold text-paw-ink">{{ $pet['name'] }}</h2>
                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-2 text-paw-coral" data-neighbor-profile-pet-meta>
                        <x-icon-text icon="paw-print">{{ $pet['breed'] }}</x-icon-text>
                        <x-icon-text icon="calendar-days">{{ $pet['age'] }}</x-icon-text>
                    </div>
                </div>
                <x-action-control
                    :label="$pet['walk_action']['label']"
                    :icon="$pet['walk_action']['icon']"
                    :variant="$pet['walk_action']['variant']"
                    :endpoint="$pet['walk_action']['endpoint']"
                    :payload="$pet['walk_action']['payload']"
                    class="shrink-0"
                />
            </div>

            <p class="mt-3 text-sm leading-6 text-paw-muted" data-neighbor-profile-pet-status>{{ $pet['status'] }}</p>

            <x-tag-list :items="$pet['traits']" :empty="$copy['traits_empty']" class="mt-4" data-neighbor-profile-pet-traits />

            <dl class="mt-5 grid gap-3 border-t border-paw-line pt-4 sm:grid-cols-3" data-neighbor-profile-routine>
                @forelse ($pet['routine'] as $item)
                    <div>
                        <dt class="flex min-w-0 items-center gap-1.5 text-xs font-semibold text-paw-muted">
                            <x-ui-icon size="sm" :name="$item['icon']" />
                            <span class="min-w-0 break-words">{{ $item['label'] }}</span>
                        </dt>
                        <dd class="mt-1 text-sm font-semibold leading-5 text-paw-ink">{{ $item['value'] }}</dd>
                    </div>
                @empty
                    <div class="text-sm text-paw-muted">{{ $copy['routine_empty'] }}</div>
                @endforelse
            </dl>
        </div>
    </div>
</section>
