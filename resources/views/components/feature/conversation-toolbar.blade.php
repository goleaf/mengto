@props(['filters', 'unreadCount', 'query' => '', 'activeFilter' => 'all'])

<form method="GET" action="{{ route('messages.index') }}" class="border-b border-paw-line p-4">
    <x-ui.panel-heading title="Conversations" :meta="$unreadCount.' unread'" />

    <x-ui.search-field
        id="conversation-search"
        label="Search conversations"
        placeholder="Search messages"
        :value="$query"
        class="mt-4"
    />

    <div class="mt-3 flex flex-wrap items-center gap-2">
        <x-ui.filter-group
            :filters="$filters"
            :active="$activeFilter"
            label="Conversation filters"
            submit
            class="flex-1"
        />
        <x-ui.action-control type="submit" label="Search" icon="search" variant="primary" size="toolbar" />
    </div>
</form>
