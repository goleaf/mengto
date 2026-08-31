<x-app-shell :title="$page_title" active-section="feed">
    <div class="mx-auto max-w-3xl">
        <x-detail-navigation
            :href="route('content.index')"
            :label="__('content.feed.back')"
            class="mb-4"
        />

        <x-content-publication-card
            :publication="$publication"
            :heading-level="1"
            show-full-body
            data-content-detail-identity
        />
    </div>
</x-app-shell>
