<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3 border-b border-paw-line pb-5">
            <div>
                <x-detail-navigation :href="route('experts.show', $expert['slug'])" :label="$expert['name']" />
                <h1 class="mt-2 text-3xl font-bold">{{ __('ui.appointment_details') }}</h1>
            </div>
            @if ($consultation && $booking['format'] === 'Video')
                <x-action-control label="{{ __('ui.open_consultation_room') }}" icon="video" variant="primary" :href="route('consultations.show', $consultation['id'])" />
            @endif
        </header>

        <x-booking-content
            :booking="$booking"
            :expert="$expert"
            :service="$service"
            :consultation="$consultation"
            :documents="$documents"
            :audit="$audit"
            :can-manage-expert="$can_manage_expert"
        />
    </div>
</x-app-shell>
