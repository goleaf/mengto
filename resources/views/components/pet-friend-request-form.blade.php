@props([
    'item',
    'endpoint',
])

<details class="friend-request">
    <summary class="friend-request__summary">
        <x-lucide-user-plus class="icon icon--sm" aria-hidden="true" />
        <span>{{ __('ui.send_friend_request_a0383a957f') }}</span>
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
            <span>{{ __('ui.connection_type_a2cfeb2898') }}</span>
            <select id="friend-request-{{ $item['key'] }}-intent" name="friendship_intent" class="field field--select" required>
                <option value="friend" @selected($item['request_form']['default_intent'] === 'friend')>{{ __('ui.general_friends_806914d5dd') }}</option>
                <option value="walk" @selected($item['request_form']['default_intent'] === 'walk')>{{ __('ui.walk_companions_ffea92097d') }}</option>
                <option value="play" @selected($item['request_form']['default_intent'] === 'play')>{{ __('ui.play_friends_e9f51431c8') }}</option>
                <option value="training" @selected($item['request_form']['default_intent'] === 'training')>{{ __('ui.training_partners_a9afdefac7') }}</option>
                <option value="neighbor" @selected($item['request_form']['default_intent'] === 'neighbor')>{{ __('ui.nearby_friends_ceee23cfd2') }}</option>
            </select>
        </label>

        <label for="friend-request-{{ $item['key'] }}-message" class="friend-request__field">
            <span>{{ __('ui.short_owner_message_fefd36340c') }}</span>
            <textarea
                id="friend-request-{{ $item['key'] }}-message"
                name="friendship_message"
                rows="3"
                maxlength="280"
                class="field field--textarea"
                placeholder="{{ __('ui.share_where_you_met_or_suggest_a_calm_37ddaba3e4') }}"
            ></textarea>
        </label>

        <label for="friend-request-{{ $item['key'] }}-met" class="friend-request__field">
            <span>{{ __('ui.where_you_may_have_met_05f56e939e') }} <small>{{ __('ui.optional_ec91fdd925') }}</small></span>
            <input
                id="friend-request-{{ $item['key'] }}-met"
                type="text"
                name="met_at"
                maxlength="120"
                class="field"
                placeholder="{{ __('ui.public_park_group_or_event_ed205d2d46') }}"
            >
        </label>

        <label class="friend-request__check">
            <input type="checkbox" name="share_area" value="yes">
            <span>{{ __('ui.share_only_my_broad_neighborhood_2a64f6bd74') }}</span>
        </label>

        <div class="friend-request__footer">
            <p>{{ __('presentation.sent_on_behalf', ['pet' => $item['request_form']['source_pet'] === 'pet-scout' ? __('ui.scout_8a1db462be') : __('ui.nori_a64203ba20')]) }}</p>
            <x-action-control
                type="submit"
                label="{{ __('ui.send_request_3a69f89729') }}"
                icon="send"
                variant="primary"
                size="regular"
            />
        </div>
    </form>
</details>
