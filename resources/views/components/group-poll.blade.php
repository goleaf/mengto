@props(['group', 'poll', 'membership'])

<x-content-panel
    section="group-poll"
    eyebrow="Member poll"
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
                    {{ $option['votes'] }} of {{ $poll['total'] }} votes
                </progress>
                <x-action-control
                    :label="$option['active'] ? 'Selected' : 'Vote'"
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
            <p role="listitem" class="group-dashboard__empty">No poll options are available yet.</p>
        @endforelse
    </div>
</x-content-panel>
