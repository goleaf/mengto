<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <header class="care-directory-header">
            <div>
                <div class="flex items-center gap-2 text-sm font-bold text-paw-leaf">
                    <x-lucide-lock-keyhole class="size-4" aria-hidden="true" />
                    <span>Private family workspace</span>
                </div>
                <h1 class="mt-3 text-3xl font-bold sm:text-4xl">Care journals</h1>
                <p class="mt-2 max-w-3xl text-paw-muted">
                    Today's feeding, water, walks, rest, toilet, activity, routines, and handoffs for every pet you manage.
                </p>
            </div>
            <x-action-control :href="route('care-journals.create')" label="Create journal" icon="plus" variant="primary" size="regular" />
        </header>

        <section class="care-family-strip" aria-label="Family care overview">
            <x-lucide-users-round class="size-5" aria-hidden="true" />
            <div>
                <strong>One place for the household</strong>
                <p>Each pet keeps a separate journal. Missing records stay marked as unknown, never silently treated as missed care.</p>
            </div>
        </section>

        <section class="care-directory-grid" aria-label="Your pet care journals">
            @forelse ($journals as $journal)
                <x-care-journal-card :journal="$journal" />
            @empty
                <div class="care-empty care-empty--wide">
                    <x-lucide-notebook-tabs class="size-8" aria-hidden="true" />
                    <h2 class="text-xl font-bold">No private care journals yet</h2>
                    <p>Create one for a pet you manage, then add structured daily actions and family tasks.</p>
                    <x-action-control :href="route('care-journals.create')" label="Create first journal" icon="plus" variant="primary" />
                </div>
            @endforelse
        </section>

        @if ($journals->hasPages())
            <div>{{ $journals->withQueryString()->links() }}</div>
        @endif
    </div>
</x-app-shell>
