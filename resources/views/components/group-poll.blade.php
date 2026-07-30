@props(['group', 'poll', 'membership'])

<x-content-panel
    section="group-poll"
    eyebrow="{{ __('ui.member_poll_4e676c585f') }}"
    :title="$poll['question']"
>
    <p class="poll__description">{{ $poll['description'] }}</p>

    <div class="poll__options" role="list">
        @forelse ($poll['options'] as $option)
            <div role="listitem" class="poll__option">
                <div class="poll__label">
                    <span>{{ $option['label'] }}</span>
                    <strong>{{ $option['votes'] }}</strong>
                </div>
                <progress value="{{ $option['votes'] }}" max="{{ $poll['total'] }}">
                    {{ __('presentation.votes_of_total', ['votes' => $option['votes'], 'total' => $poll['total']]) }}
                </progress>
                <x-action-control
                    :label="$option['active'] ? __('ui.selected_57fd7a0cf3') : __('ui.vote_cd5588db6f')"
                    :icon="$option['active'] ? 'check' : 'circle'"
                    :endpoint="$membership === 'joined' ? route('actions.perform') : null"
                    :payload="$option['payload']"
                    :active="$option['active']"
                    :pressed="$option['active']"
                    :disabled="$membership !== 'joined'"
                    variant="paper"
                    size="compact"
                />
            </div>
        @empty
            <p role="listitem" class="group-dashboard__empty">{{ __('ui.no_poll_options_are_available_yet_589d1d168f') }}</p>
        @endforelse
    </div>
</x-content-panel>
