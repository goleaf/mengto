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
            label="{{ __('ui.open_messages_cf997592c9') }}"
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
            label="{{ __('ui.cancel_plan_2e5d129831') }}"
            icon="x"
            variant="quiet"
            size="regular"
        />
    @else
        <x-action-control
            :href="route('compose', 'walk')"
            label="{{ __('ui.plan_another_d34fe3c272') }}"
            icon="calendar-plus"
            variant="paper"
            size="regular"
        />
    @endif
</footer>
