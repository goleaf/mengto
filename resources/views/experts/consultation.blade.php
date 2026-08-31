<x-app-shell :title="$page_title" :active-section="$active_section">
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3 border-b border-paw-line pb-5">
            <div>
                <x-detail-navigation
                    :href="route('bookings.show', $booking['reference'])"
                    :label="__('ui.appointment_details')"
                />
                <h1 class="mt-2 text-3xl font-bold">{{ __('ui.video_consultation') }}</h1>
            </div>
            <x-status-badge label="{{ __('ui.not_an_emergency_service') }}" icon="siren" tone="warning" />
        </header>

        <x-booking-content
            :booking="$booking"
            :expert="$expert"
            :service="$service"
            :consultation="$consultation"
            :documents="$documents"
            :audit="$audit"
            :can-manage-expert="$can_manage_expert"
            :consultation-mode="true"
        />
    </div>
</x-app-shell>
