@props(['plan'])

<footer {{ $attributes->class('walk-actions') }}>
    @if ($plan['next_action'])
        <x-action-control
            :endpoint="route('actions.perform')"
            :payload="[
                'action' => 'advance-walk-plan',
                'target' => $plan['id'],
                'label' => $plan['title'],
            ]"
            :label="$plan['next_action']['label']"
            :icon="$plan['next_action']['icon']"
            variant="primary"
            size="regular"
        />
    @endif

    @if ($plan['conversation'])
        <x-action-control
            :href="route('messages.index', ['conversation' => $plan['conversation']])"
            label="Open messages"
            icon="message-circle"
            variant="paper"
            size="regular"
        />
    @endif

    @if (in_array($plan['status'], ['draft', 'confirmed'], true))
        <x-action-control
            :endpoint="route('actions.perform')"
            :payload="[
                'action' => 'cancel-walk-plan',
                'target' => $plan['id'],
                'label' => $plan['title'],
            ]"
            label="Cancel plan"
            icon="x"
            variant="quiet"
            size="regular"
        />
    @else
        <x-action-control
            :href="route('compose', 'walk')"
            label="Plan another"
            icon="calendar-plus"
            variant="paper"
            size="regular"
        />
    @endif
</footer>
