<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <header class="flex flex-col gap-4 border-b border-paw-line pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase text-paw-leaf">Private care workspace</p>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Pet health records</h1>
                <p class="mt-3 max-w-3xl leading-7 text-paw-muted">
                    Vaccinations, medication schedules, measurements, visits, and original documents.
                </p>
            </div>
            <x-action-control label="New health record" icon="plus" variant="primary" :href="route('medical-records.create')" />
        </header>

        <section class="medical-privacy-strip" aria-label="Medical privacy status">
            <x-lucide-lock-keyhole class="size-5" aria-hidden="true" />
            <div>
                <strong>Medical data is private by default</strong>
                <span>Pet followers, social groups, marketplace sellers, and unrelated specialists cannot open these records.</span>
            </div>
        </section>

        <section aria-labelledby="health-record-list-title">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 id="health-record-list-title" class="text-2xl font-bold">Managed records</h2>
                <span class="text-sm font-semibold text-paw-muted">Owner: {{ $owner['name'] }}</span>
            </div>

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($records as $record)
                    <x-medical-record-card :record="$record" />
                @empty
                    <div class="medical-empty min-h-64 md:col-span-2 xl:col-span-3">
                        <x-lucide-heart-pulse class="size-9" aria-hidden="true" />
                        <h2 class="text-xl font-bold">No health records yet</h2>
                        <x-action-control label="Create the first record" icon="plus" variant="primary" :href="route('medical-records.create')" />
                    </div>
                @endforelse
            </div>

            <div class="mt-5">
                {{ $records->links() }}
            </div>
        </section>
    </div>
</x-app-shell>
