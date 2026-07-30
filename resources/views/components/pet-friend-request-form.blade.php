@props([
    'item',
    'endpoint',
])

@php
    $form = $item['request_form'];
    $formId = 'friend-request-'.$item['key'];
@endphp

<details class="friend-request">
    <summary class="friend-request__summary">
        <x-lucide-user-plus class="icon icon--sm" aria-hidden="true" />
        <span>Send friend request</span>
        <x-lucide-chevron-down class="icon icon--sm friend-request__chevron" aria-hidden="true" />
    </summary>

    <form method="POST" action="{{ $endpoint }}" class="friend-request__form">
        @csrf
        <input type="hidden" name="action" value="{{ $form['action'] }}">
        <input type="hidden" name="source_pet" value="{{ $form['source_pet'] }}">
        <input type="hidden" name="target" value="{{ $form['target'] }}">
        <input type="hidden" name="label" value="{{ $item['name'] }}">
        @forelse ($form['return_state'] as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @empty
        @endforelse

        <label for="{{ $formId }}-intent" class="friend-request__field">
            <span>Connection type</span>
            <select id="{{ $formId }}-intent" name="friendship_intent" class="field field--select" required>
                <option value="friend" @selected($form['default_intent'] === 'friend')>General friends</option>
                <option value="walk" @selected($form['default_intent'] === 'walk')>Walk companions</option>
                <option value="play" @selected($form['default_intent'] === 'play')>Play friends</option>
                <option value="training" @selected($form['default_intent'] === 'training')>Training partners</option>
                <option value="neighbor" @selected($form['default_intent'] === 'neighbor')>Nearby friends</option>
            </select>
        </label>

        <label for="{{ $formId }}-message" class="friend-request__field">
            <span>Short owner message</span>
            <textarea
                id="{{ $formId }}-message"
                name="friendship_message"
                rows="3"
                maxlength="280"
                class="field field--textarea"
                placeholder="Share where you met or suggest a calm first activity."
            ></textarea>
        </label>

        <label for="{{ $formId }}-met" class="friend-request__field">
            <span>Where you may have met <small>optional</small></span>
            <input
                id="{{ $formId }}-met"
                type="text"
                name="met_at"
                maxlength="120"
                class="field"
                placeholder="Public park, group, or event"
            >
        </label>

        <label class="friend-request__check">
            <input type="checkbox" name="share_area" value="yes">
            <span>Share only my broad neighborhood</span>
        </label>

        <div class="friend-request__footer">
            <p>Sent by Mia on behalf of {{ $item['request_form']['source_pet'] === 'pet-scout' ? 'Scout' : 'Nori' }}.</p>
            <x-action-control
                type="submit"
                label="Send request"
                icon="send"
                variant="primary"
                size="regular"
            />
        </div>
    </form>
</details>
