@props(['professional'])

<section class="messaging-professional" aria-label="{{ __('messaging.professional.status_label') }}" data-messaging-professional>
    <div>
        <x-ui-icon name="briefcase-medical" />
        <span>
            <strong>{{ $professional['case'] }}</strong>
            <small>{{ $professional['status'] }} · {{ $professional['assigned'] }}</small>
        </span>
    </div>
    <div>
        <span>{{ $professional['hours'] }}</span>
        <strong>{{ $professional['queue'] }}</strong>
    </div>
    <p><x-ui-icon name="triangle-alert" size="sm" /> {{ $professional['urgent'] }}</p>
</section>
