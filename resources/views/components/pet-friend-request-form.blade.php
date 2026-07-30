@props([
    'item',
    'endpoint',
])

<details class="friend-request">
    <summary class="friend-request__summary">
        <x-lucide-user-plus class="icon icon--sm" aria-hidden="true" />
        <span>Send friend request</span>
        <x-lucide-chevron-down class="icon icon--sm friend-request__chevron" aria-hidden="true" />
    </summary>

    <form method="POST" action="{{ $endpoint }}" class="friend-request__form">
        @csrf
        <input type="hidden" name="action" value="{{ $item['request_form']['action'] }}">
        <input type="hidden" name="source_pet" value="{{ $item['request_form']['source_pet'] }}">
        <input type="hidden" name="target" value="{{ $item['request_form']['target'] }}">
        <input type="hidden" name="label" value="{{ $item['name'] }}">
        @forelse ($item['request_form']['return_state'] as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @empty
        @endforelse

        <label for="friend-request-{{ $item['key'] }}-intent" class="friend-request__field">
            <span>Connection type</span>
            <select id="friend-request-{{ $item['key'] }}-intent" name="friendship_intent" class="field field--select" required>
                <option value="friend" @selected($item['request_form']['default_intent'] === 'friend')>General friends</option>
                <option value="walk" @selected($item['request_form']['default_intent'] === 'walk')>Walk companions</option>
                <option value="play" @selected($item['request_form']['default_intent'] === 'play')>Play friends</option>
                <option value="training" @selected($item['request_form']['default_intent'] === 'training')>Training partners</option>
                <option value="neighbor" @selected($item['request_form']['default_intent'] === 'neighbor')>Nearby friends</option>
            </select>
        </label>

        <label for="friend-request-{{ $item['key'] }}-message" class="friend-request__field">
            <span>Short owner message</span>
            <textarea
                id="friend-request-{{ $item['key'] }}-message"
                name="friendship_message"
                rows="3"
                maxlength="280"
                class="field field--textarea"
                placeholder="Share where you met or suggest a calm first activity."
            ></textarea>
        </label>

        <label for="friend-request-{{ $item['key'] }}-met" class="friend-request__field">
            <span>Where you may have met <small>optional</small></span>
            <input
                id="friend-request-{{ $item['key'] }}-met"
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
