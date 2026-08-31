<x-app-shell :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <x-page-header
            :eyebrow="__('ui.private_care_workspace')"
            :title="__('ui.pet_health_records')"
            :description="__('ui.vaccinations_medication_schedules_measurements_visits_and_original_documents')"
            heading-id="medical-records-heading"
            :action-label="__('ui.new_health_record')"
            action-icon="plus"
            :action-href="route('medical-records.create')"
            data-section="medical-records-header"
        />

        <section class="medical-privacy-strip" aria-label="{{ __('ui.medical_privacy_status') }}">
            <x-ui-icon name="lock-keyhole" size="lg" />
            <div>
                <strong>{{ __('ui.medical_data_is_private_by_default') }}</strong>
                <span>{{ __('ui.pet_followers_social_groups_marketplace_sellers_and_unrelated_specialists_cannot_open_these_records') }}</span>
            </div>
        </section>

        <section aria-labelledby="health-record-list-title">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 id="health-record-list-title" class="text-2xl font-bold">{{ __('ui.managed_records') }}</h2>
                <span class="text-sm font-semibold text-paw-muted">{{ __('presentation.owner', ['name' => $owner['name']]) }}</span>
            </div>

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($records as $record)
                    <x-medical-record-card :record="$record" />
                @empty
                    <div class="medical-empty min-h-64 md:col-span-2 xl:col-span-3">
                        <x-ui-icon name="heart-pulse" size="3xl" />
                        <h2 class="text-xl font-bold">{{ __('ui.no_health_records_yet') }}</h2>
                        <x-action-control label="{{ __('ui.create_the_first_record') }}" icon="plus" variant="primary" :href="route('medical-records.create')" />
                    </div>
                @endforelse
            </div>

            <div class="mt-5">
                {{ $records->links() }}
            </div>
        </section>
    </div>
</x-app-shell>
