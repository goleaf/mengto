<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3 border-b border-paw-line pb-5">
            <div>
                <a href="{{ route('experts.show', $expert['slug']) }}" class="inline-flex items-center gap-2 text-sm font-bold text-paw-leaf"><x-lucide-arrow-left class="size-4" aria-hidden="true" />{{ $expert['name'] }}</a>
                <h1 class="mt-2 text-3xl font-bold">Appointment details</h1>
            </div>
            @if ($consultation && $booking['format'] === 'Video')
                <x-action-control label="Open consultation room" icon="video" variant="primary" :href="route('consultations.show', $consultation['id'])" />
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
