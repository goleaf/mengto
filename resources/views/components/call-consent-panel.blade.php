@props([
    'call',
    'contact',
])

<section
    aria-labelledby="call-consent-title"
    {{ $attributes->class(['panel', 'call-consent']) }}
>
    <div class="call-consent__icon" aria-hidden="true">
        <x-ui-icon :name="$call['icon']" />
    </div>

    <div class="call-consent__copy">
        <p class="call-consent__eyebrow">{{ __('ui.voice_consent_b4d220633f') }}</p>
        <h2 id="call-consent-title" class="call-consent__title">{{ $call['title'] }}</h2>
        <p class="call-consent__description">{{ $call['description'] }}</p>
    </div>

    <x-action-group class="call-consent__actions">
        <x-action-control
            :href="route('messages.index', ['conversation' => $contact['key']])"
            label="{{ __('ui.keep_messaging_23ed9808b6') }}"
            icon="message-circle"
            variant="paper"
            size="regular"
        />
        <x-action-control
            :endpoint="route('actions.perform')"
            :payload="['action' => 'call', 'target' => $contact['key'], 'label' => $contact['title']]"
            :label="$call['label']"
            :icon="$call['icon']"
            :active="$call['requested']"
            :pressed="$call['requested']"
            variant="primary"
            size="regular"
        />
    </x-action-group>
</section>
