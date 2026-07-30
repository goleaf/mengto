<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <header class="flex flex-col gap-4 border-b border-paw-line pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase text-paw-leaf">{{ __('ui.private_care_workspace_12776f8bcf') }}</p>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ __('ui.pet_health_records_911c3e19be') }}</h1>
                <p class="mt-3 max-w-3xl leading-7 text-paw-muted">
                    {{ __('ui.vaccinations_medication_schedules_measurements_visits_and_original_docum_72518c6620') }}
                </p>
            </div>
            <x-action-control label="{{ __('ui.new_health_record_376edfa614') }}" icon="plus" variant="primary" :href="route('medical-records.create')" />
        </header>

        <section class="medical-privacy-strip" aria-label="{{ __('ui.medical_privacy_status_a8a40a4bd3') }}">
            <x-lucide-lock-keyhole class="size-5" aria-hidden="true" />
            <div>
                <strong>{{ __('ui.medical_data_is_private_by_default_2829377998') }}</strong>
                <span>{{ __('ui.pet_followers_social_groups_marketplace_sellers_and_unrelated_62afc5fdb8') }}</span>
            </div>
        </section>

        <section aria-labelledby="health-record-list-title">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 id="health-record-list-title" class="text-2xl font-bold">{{ __('ui.managed_records_663bb02e0d') }}</h2>
                <span class="text-sm font-semibold text-paw-muted">{{ __('presentation.owner', ['name' => $owner['name']]) }}</span>
            </div>

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($records as $record)
                    <x-medical-record-card :record="$record" />
                @empty
                    <div class="medical-empty min-h-64 md:col-span-2 xl:col-span-3">
                        <x-lucide-heart-pulse class="size-9" aria-hidden="true" />
                        <h2 class="text-xl font-bold">{{ __('ui.no_health_records_yet_8cc935a87c') }}</h2>
                        <x-action-control label="{{ __('ui.create_the_first_record_381606f2e5') }}" icon="plus" variant="primary" :href="route('medical-records.create')" />
                    </div>
                @endforelse
            </div>

            <div class="mt-5">
                {{ $records->links() }}
            </div>
        </section>
    </div>
</x-app-shell>
