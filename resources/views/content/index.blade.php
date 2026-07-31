<x-app-shell :owner="$owner" :title="$page_title" active-section="feed">
    <div class="mx-auto max-w-3xl">
        <header class="mb-5 border-b border-paw-line pb-4">
            <h1 class="text-2xl font-bold text-paw-ink">{{ __('content.feed.heading') }}</h1>
            <p class="mt-2 text-sm leading-6 text-paw-muted">{{ __('content.feed.chronological_label') }}</p>
        </header>

        <div role="feed" aria-label="{{ __('content.feed.heading') }}" class="space-y-4">
            @forelse ($feed['items'] as $publication)
                <x-content-publication-card :publication="$publication" />
            @empty
                <x-empty-state
                    icon="newspaper"
                    :title="$empty_title"
                    :description="$empty_description"
                />
            @endforelse
        </div>

        @if ($feed['previous_url'] || $feed['next_url'])
            <nav aria-label="{{ __('content.feed.pagination') }}" class="mt-6 flex items-center justify-between gap-3">
                @if ($feed['previous_url'])
                    <a href="{{ $feed['previous_url'] }}" class="button button--secondary">
                        <x-lucide-arrow-left class="size-4" aria-hidden="true" />
                        {{ __('content.feed.newer') }}
                    </a>
                @else
                    <span></span>
                @endif

                @if ($feed['next_url'])
                    <a href="{{ $feed['next_url'] }}" class="button button--secondary">
                        {{ __('content.feed.older') }}
                        <x-lucide-arrow-right class="size-4" aria-hidden="true" />
                    </a>
                @endif
            </nav>
        @endif
    </div>
</x-app-shell>
