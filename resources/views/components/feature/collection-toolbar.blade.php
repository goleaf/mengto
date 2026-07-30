@props([
    'action',
    'filters',
    'active',
    'count',
    'title',
    'label',
])

<form method="GET" action="{{ $action }}" {{ $attributes->class('collection-toolbar') }}>
    <x-ui.panel-heading :title="$title" :meta="$count" />

    <x-ui.filter-group
        :filters="$filters"
        :active="$active"
        :label="$label"
        submit
    />
</form>
