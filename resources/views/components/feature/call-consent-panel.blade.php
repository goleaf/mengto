@props([
    'call',
    'contact',
])

<section
    aria-labelledby="call-consent-title"
    {{ $attributes->class(['panel', 'call-consent']) }}
>
    <div class="call-consent__icon" aria-hidden="true">
        <x-dynamic-component :component="'lucide-'.$call['icon']" class="icon" />
    </div>

    <div class="call-consent__copy">
        <p class="call-consent__eyebrow">Voice consent</p>
        <h2 id="call-consent-title" class="call-consent__title">{{ $call['title'] }}</h2>
        <p class="call-consent__description">{{ $call['description'] }}</p>
    </div>

    <x-ui.action-group class="call-consent__actions">
        <x-ui.action-control
            :href="route('pet-social.messages.index', ['conversation' => $contact['key']])"
            label="Keep messaging"
            icon="message-circle"
            variant="paper"
            size="regular"
        />
        <x-ui.action-control
            :endpoint="route('pet-social.actions.perform')"
            :payload="['action' => 'call', 'target' => $contact['key'], 'label' => $contact['title']]"
            :label="$call['label']"
            :icon="$call['icon']"
            :active="$call['requested']"
            :pressed="$call['requested']"
            variant="primary"
            size="regular"
        />
    </x-ui.action-group>
</section>
