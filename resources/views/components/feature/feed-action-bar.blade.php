@props(['post'])

<div {{ $attributes->class(['feed-actions']) }}>
    @if (isset($post['reaction_options']))
        <x-feature.reaction-picker :post="$post" />
        <x-feature.feed-action
            :label="$post['reply_total'].' '.\Illuminate\Support\Str::plural('Comment', $post['reply_total'])"
            :compact-label="$post['reply_total']"
            icon="message-circle"
            :href="$post['thread_url']"
        />
        <x-feature.feed-action
            :label="$post['reposts'].' '.\Illuminate\Support\Str::plural('Repost', $post['reposts'])"
            compact-label="Repost"
            icon="repeat-2"
            :endpoint="route('actions.perform')"
            :payload="['action' => 'repost-post', 'target' => $post['key'], 'label' => $post['represented'].' publication']"
        />
        <x-feature.feed-action
            label="Save"
            active-label="Saved"
            icon="bookmark"
            :active="$post['saved']"
            :endpoint="route('actions.perform')"
            :payload="['action' => 'toggle-save', 'target' => $post['key'], 'label' => $post['represented'].' publication']"
        />
    @else
    <x-feature.feed-action
        :label="$post['stats']['paws'].' Paws'"
        :compact-label="$post['stats']['paws']"
        active-label="Pawed"
        icon="paw-print"
        :active="$post['pawed']"
        :endpoint="route('actions.perform')"
        :payload="['action' => 'toggle-paw', 'target' => $post['key'], 'label' => $post['pet'].' moment']"
    />
    <x-feature.feed-action
        :label="$post['stats']['replies'].' '.\Illuminate\Support\Str::plural('Reply', (int) $post['stats']['replies'])"
        :compact-label="$post['stats']['replies']"
        icon="message-circle"
        :href="route('posts.show', ['post' => $post['key']])"
    />
    <x-feature.feed-action
        label="Share"
        icon="share-2"
        :endpoint="route('actions.perform')"
        :payload="['action' => 'share', 'target' => $post['key'], 'label' => $post['pet'].' moment']"
    />
    <x-feature.feed-action
        label="Save"
        active-label="Saved"
        icon="bookmark"
        :active="$post['saved']"
        :endpoint="route('actions.perform')"
        :payload="['action' => 'toggle-save', 'target' => $post['key'], 'label' => $post['pet'].' moment']"
    />
    @endif
</div>
