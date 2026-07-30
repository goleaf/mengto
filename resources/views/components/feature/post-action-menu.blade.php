@props(['post'])

<details class="post-menu">
    <summary aria-label="Publication actions" title="Publication actions">
        <x-lucide-ellipsis class="icon icon--sm" aria-hidden="true" />
    </summary>

    <div class="post-menu__items">
        <a href="{{ $post['share_url'] }}">
            <x-lucide-share-2 class="icon icon--sm" aria-hidden="true" />
            Share
        </a>

        <x-ui.action-form
            :action="route('actions.perform')"
            :payload="[
                'action' => 'toggle-post-subscription',
                'target' => $post['key'],
                'label' => $post['represented'].' publication',
            ]"
        >
            <button type="submit">
                <x-lucide-bell class="icon icon--sm" aria-hidden="true" />
                {{ $post['subscribed'] ? 'Pause updates' : 'Follow updates' }}
            </button>
        </x-ui.action-form>

        @if ($post['can_manage'])
            <a href="{{ $post['edit_url'] }}">
                <x-lucide-pencil class="icon icon--sm" aria-hidden="true" />
                Edit
            </a>
            <x-ui.action-form
                :action="route('actions.perform')"
                :payload="[
                    'action' => $post['status'] === 'archived' ? 'restore-post' : 'archive-post',
                    'target' => $post['key'],
                ]"
            >
                <button type="submit">
                    <x-dynamic-component
                        :component="'lucide-'.($post['status'] === 'archived' ? 'archive-restore' : 'archive')"
                        class="icon icon--sm"
                        aria-hidden="true"
                    />
                    {{ $post['status'] === 'archived' ? 'Restore' : 'Archive' }}
                </button>
            </x-ui.action-form>
            <a
                href="{{ route('compose', ['kind' => 'delete-post', 'post' => $post['key']]) }}"
                class="post-menu__danger"
            >
                <x-lucide-trash-2 class="icon icon--sm" aria-hidden="true" />
                Delete
            </a>
        @else
            <x-ui.action-form
                :action="route('actions.perform')"
                :payload="['action' => 'hide-post', 'target' => $post['key'], 'label' => 'Publication']"
            >
                <button type="submit">
                    <x-lucide-eye-off class="icon icon--sm" aria-hidden="true" />
                    Not interested
                </button>
            </x-ui.action-form>
            <x-ui.action-form
                :action="route('actions.perform')"
                :payload="['action' => 'mute-author', 'target' => $post['key']]"
            >
                <button type="submit">
                    <x-lucide-volume-x class="icon icon--sm" aria-hidden="true" />
                    Mute author
                </button>
            </x-ui.action-form>
            <x-ui.action-form
                :action="route('actions.perform')"
                :payload="['action' => 'block-post-author', 'target' => $post['key']]"
            >
                <button type="submit" class="post-menu__danger">
                    <x-lucide-ban class="icon icon--sm" aria-hidden="true" />
                    Block author
                </button>
            </x-ui.action-form>
            <a href="{{ $post['report_url'] }}" class="post-menu__danger">
                <x-lucide-flag class="icon icon--sm" aria-hidden="true" />
                Report
            </a>
        @endif
    </div>
</details>
