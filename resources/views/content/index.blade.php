<x-app-shell :owner="$owner" :title="$page_title" active-section="feed">
    <div class="mx-auto max-w-3xl">
        <x-page-header
            :eyebrow="__('content.feed.page_title')"
            :title="__('content.feed.heading')"
            :description="__('content.feed.chronological_label')"
            heading-id="content-feed-heading"
            data-section="content-feed-header"
            class="mb-5"
        />

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
