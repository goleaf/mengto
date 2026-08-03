<x-app-shell :owner="$owner" :title="$page_title" active-section="feed">
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('content.index') }}" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-paw-leaf hover:underline">
            <x-ui-icon name="arrow-left" size="sm" />
            {{ __('content.feed.back') }}
        </a>

        <x-content-publication-card :publication="$publication" :heading-level="1" show-full-body />
    </div>
</x-app-shell>
