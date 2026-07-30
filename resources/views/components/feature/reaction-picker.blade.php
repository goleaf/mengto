@props(['post'])

<details class="reaction-picker">
    <summary
        class="feed-action"
        aria-label="{{ $post['selected_reaction_label'] ?? 'React' }}"
        title="{{ $post['selected_reaction_label'] ?? 'React' }}"
    >
        <x-lucide-heart class="icon icon--sm" aria-hidden="true" />
        <span class="feed-action__label">{{ $post['selected_reaction_label'] ?? 'React' }}</span>
        <span class="feed-action__compact-label" aria-hidden="true">{{ $post['reaction_total'] }}</span>
    </summary>

    <div class="reaction-picker__menu" aria-label="Choose reaction">
        @foreach ($post['reaction_items'] as $reaction)
            <x-ui.action-form
                :action="route('pet-social.actions.perform')"
                :payload="['action' => 'set-reaction', 'target' => $post['key'], 'reaction' => $reaction['value']]"
            >
                <button
                    type="submit"
                    @if ($reaction['selected']) aria-pressed="true" @endif
                    class="reaction-picker__option"
                >
                    <x-dynamic-component
                        :component="'lucide-'.$reaction['icon']"
                        class="icon icon--sm"
                        aria-hidden="true"
                    />
                    <span>{{ $reaction['label'] }}</span>
                    <small>{{ $reaction['count'] }}</small>
                </button>
            </x-ui.action-form>
        @endforeach
    </div>
</details>
