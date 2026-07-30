@props(['filters', 'unreadCount', 'query' => '', 'activeFilter' => 'all'])

<form method="GET" action="{{ route('messages.index') }}" class="border-b border-paw-line p-4">
    <x-panel-heading title="{{ __('ui.conversations_1d432f5869') }}" :meta="__('presentation.unread_count', ['count' => $unreadCount])" />

    <x-search-field
        id="conversation-search"
        label="{{ __('ui.search_conversations_8abdf3b226') }}"
        placeholder="{{ __('ui.search_messages_ddf0602b21') }}"
        :value="$query"
        class="mt-4"
    />

    <div class="mt-3 flex flex-wrap items-center gap-2">
        <x-filter-group
            :filters="$filters"
            :active="$activeFilter"
            label="{{ __('ui.conversation_filters_0f525e6dfe') }}"
            submit
            class="flex-1"
        />
        <x-action-control type="submit" label="{{ __('ui.search_49c266baaa') }}" icon="search" variant="primary" size="toolbar" />
    </div>
</form>
