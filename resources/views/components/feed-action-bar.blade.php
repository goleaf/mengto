@props(['post'])

<div {{ $attributes->class(['feed-actions']) }}>
    @if (isset($post['reaction_options']))
        <x-reaction-picker :post="$post" />
        <x-feed-action
            :label="trans_choice('presentation.comments_count', $post['reply_total'], ['count' => $post['reply_total']])"
            :compact-label="$post['reply_total']"
            icon="message-circle"
            :href="$post['thread_url']"
        />
        <x-feed-action
            :label="trans_choice('presentation.reposts_count', $post['reposts'], ['count' => $post['reposts']])"
            compact-label="{{ __('ui.repost_f4fd9adb8f') }}"
            icon="repeat-2"
            :endpoint="route('actions.perform')"
            :payload="['action' => 'repost-post', 'target' => $post['key'], 'label' => __('presentation.action_publication', ['name' => $post['represented']])]"
        />
        <x-feed-action
            label="{{ __('ui.save_1509f561f2') }}"
            active-label="{{ __('ui.saved_b5c120b316') }}"
            icon="bookmark"
            :active="$post['saved']"
            :endpoint="route('actions.perform')"
            :payload="['action' => 'toggle-save', 'target' => $post['key'], 'label' => __('presentation.action_publication', ['name' => $post['represented']])]"
        />
    @else
    <x-feed-action
        :label="$post['stats']['paws'].' '.__('ui.paws_45f20e8148')"
        :compact-label="$post['stats']['paws']"
        active-label="{{ __('ui.pawed_73bec350db') }}"
        icon="paw-print"
        :active="$post['pawed']"
        :endpoint="route('actions.perform')"
        :payload="['action' => 'toggle-paw', 'target' => $post['key'], 'label' => __('presentation.action_moment', ['pet' => $post['pet']])]"
    />
    <x-feed-action
        :label="trans_choice('presentation.replies_count', (int) $post['stats']['replies'], ['count' => $post['stats']['replies']])"
        :compact-label="$post['stats']['replies']"
        icon="message-circle"
        :href="route('posts.show', ['post' => $post['key']])"
    />
    <x-feed-action
        label="{{ __('ui.share_29887a5ff9') }}"
        icon="share-2"
        :endpoint="route('actions.perform')"
        :payload="['action' => 'share', 'target' => $post['key'], 'label' => __('presentation.action_moment', ['pet' => $post['pet']])]"
    />
    <x-feed-action
        label="{{ __('ui.save_1509f561f2') }}"
        active-label="{{ __('ui.saved_b5c120b316') }}"
        icon="bookmark"
        :active="$post['saved']"
        :endpoint="route('actions.perform')"
        :payload="['action' => 'toggle-save', 'target' => $post['key'], 'label' => __('presentation.action_moment', ['pet' => $post['pet']])]"
    />
    @endif
</div>
