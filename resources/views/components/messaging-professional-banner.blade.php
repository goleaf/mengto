@props(['professional'])

<section class="messaging-professional" aria-label="Professional conversation status">
    <div>
        <x-lucide-briefcase-medical class="icon" aria-hidden="true" />
        <span>
            <strong>{{ $professional['case'] }}</strong>
            <small>{{ $professional['status'] }} · {{ $professional['assigned'] }}</small>
        </span>
    </div>
    <div>
        <span>{{ $professional['hours'] }}</span>
        <strong>{{ $professional['queue'] }}</strong>
    </div>
    <p><x-lucide-triangle-alert class="icon icon--sm" aria-hidden="true" /> {{ $professional['urgent'] }}</p>
</section>
