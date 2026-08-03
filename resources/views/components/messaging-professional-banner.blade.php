@props(['professional'])

<section class="messaging-professional" aria-label="{{ __('ui.professional_conversation_status_3f38e4c701') }}">
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
