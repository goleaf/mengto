@props(['post'])

<details class="post-menu">
    <summary aria-label="{{ __('ui.publication_actions_6d7a4a24b1') }}" title="{{ __('ui.publication_actions_6d7a4a24b1') }}">
        <x-ui-icon name="ellipsis" size="sm" />
    </summary>

    <div class="post-menu__items">
        <a href="{{ $post['share_url'] }}">
            <x-ui-icon name="share-2" size="sm" />
            {{ __('ui.share_29887a5ff9') }}
        </a>

        <x-action-form
            :action="route('actions.perform')"
            :payload="[
                'action' => 'toggle-post-subscription',
                'target' => $post['key'],
                'label' => __('presentation.action_publication', ['name' => $post['represented']]),
            ]"
        >
            <button type="submit">
                <x-ui-icon name="bell" size="sm" />
                {{ $post['subscribed'] ? __('ui.pause_updates_28fdf83bd3') : __('ui.follow_updates_2fea7c083c') }}
            </button>
        </x-action-form>

        @if ($post['can_manage'])
            <a href="{{ $post['edit_url'] }}">
                <x-ui-icon name="pencil" size="sm" />
                {{ __('ui.edit_464c4ffd01') }}
            </a>
            <x-action-form
                :action="route('actions.perform')"
                :payload="[
                    'action' => $post['status'] === 'archived' ? 'restore-post' : 'archive-post',
                    'target' => $post['key'],
                ]"
            >
                <button type="submit">
                    <x-ui-icon size="sm" :name="($post['status'] === 'archived' ? 'archive-restore' : 'archive')" />
                    {{ $post['status'] === 'archived' ? __('ui.restore_a76e13b983') : __('ui.archive_66f4804ee2') }}
                </button>
            </x-action-form>
            <a
                href="{{ route('compose', ['kind' => 'delete-post', 'post' => $post['key']]) }}"
                class="post-menu__danger"
            >
                <x-ui-icon name="trash-2" size="sm" />
                {{ __('ui.delete_e2d0a54968') }}
            </a>
        @else
            <x-action-form
                :action="route('actions.perform')"
                :payload="['action' => 'hide-post', 'target' => $post['key'], 'label' => __('ui.publication_15fc65b693')]"
            >
                <button type="submit">
                    <x-ui-icon name="eye-off" size="sm" />
                    {{ __('ui.not_interested_7991fb9792') }}
                </button>
            </x-action-form>
            <x-action-form
                :action="route('actions.perform')"
                :payload="['action' => 'mute-author', 'target' => $post['key']]"
            >
                <button type="submit">
                    <x-ui-icon name="volume-x" size="sm" />
                    {{ __('ui.mute_author_967425c8a1') }}
                </button>
            </x-action-form>
            <x-action-form
                :action="route('actions.perform')"
                :payload="['action' => 'block-post-author', 'target' => $post['key']]"
            >
                <button type="submit" class="post-menu__danger">
                    <x-ui-icon name="ban" size="sm" />
                    {{ __('ui.block_author_0252bd42c3') }}
                </button>
            </x-action-form>
            <a href="{{ $post['report_url'] }}" class="post-menu__danger">
                <x-ui-icon name="flag" size="sm" />
                {{ __('ui.report_b6ce788d97') }}
            </a>
        @endif
    </div>
</details>
