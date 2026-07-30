@props(['results'])

<section data-section="discover-results" aria-labelledby="discover-results-title">
    <div class="flex items-end justify-between gap-4">
        <x-section-heading
            eyebrow="Best nearby"
            title="Top matches"
            title-id="discover-results-title"
            size="directory"
        />
        <p class="text-xs font-semibold text-paw-muted">Sorted for Mia & Scout</p>
    </div>

    <div role="list" class="mt-4 grid gap-4">
        @forelse ($results as $result)
            <x-discover-result :result="$result" :eager="$loop->first" />
        @empty
            <x-empty-state
                icon="search-x"
                title="No nearby matches"
                role="listitem"
                description="Try a broader search or another category."
                :href="route('discover.index')"
            />
        @endforelse
    </div>
</section>
