<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3 border-b border-paw-line pb-5">
            <div>
                <a href="{{ route('bookings.show', $booking['reference']) }}" class="inline-flex items-center gap-2 text-sm font-bold text-paw-leaf"><x-lucide-arrow-left class="size-4" aria-hidden="true" />{{ __('ui.appointment_details_f938bdb863') }}</a>
                <h1 class="mt-2 text-3xl font-bold">{{ __('ui.video_consultation_06e6184efc') }}</h1>
            </div>
            <x-status-badge label="{{ __('ui.not_an_emergency_service_4bfaa6bb36') }}" icon="siren" tone="warning" />
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
