@props(['result', 'eager' => false])

<article role="listitem" data-discover-result class="panel panel--clip grid sm:grid-cols-[11rem_minmax(0,1fr)]">
    <x-ui.responsive-image
        :src="$result['image']"
        :small="$result['image_small'] ?? null"
        :medium="$result['image_medium'] ?? null"
        :alt="$result['image_alt']"
        :width="1200"
        :height="800"
        sizes="(min-width: 640px) 176px, calc(100vw - 2rem)"
        :eager="$eager"
        class="aspect-[16/9] w-full object-cover sm:aspect-auto sm:h-full"
    />

    <div class="min-w-0 p-4 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <p class="text-xs font-semibold uppercase text-paw-coral">{{ $result['kind'] }}</p>
            <p class="text-xs font-semibold text-paw-muted">{{ $result['meta'] }}</p>
        </div>

        <h3 class="mt-2 break-words text-lg font-semibold text-paw-ink">
            <x-ui.text-link :href="route($result['route'])">
                {{ $result['title'] }}
            </x-ui.text-link>
        </h3>
        <p class="mt-2 text-sm leading-6 text-paw-muted">{{ $result['description'] }}</p>

        <x-ui.tag-list :items="$result['tags']" empty="Open to local connections." class="mt-3" />

        <p class="mt-4 border-t border-paw-line pt-3 text-xs font-semibold text-paw-muted">{{ $result['detail'] }}</p>
    </div>
</article>
