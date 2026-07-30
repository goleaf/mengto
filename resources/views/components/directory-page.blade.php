@props([
    'owner',
    'title',
    'activeSection',
    'summary',
    'headerSection',
    'actionLabel' => null,
    'actionIcon' => null,
    'actionHref' => null,
])

<x-app-shell :owner="$owner" :title="$title" :active-section="$activeSection">
    <div {{ $attributes->class(['grid gap-5']) }}>
        <x-page-header
            :eyebrow="$summary['eyebrow']"
            :title="$summary['title']"
            :description="$summary['description']"
            :count="$summary['count']"
            :action-label="$actionLabel"
            :action-icon="$actionIcon"
            :action-href="$actionHref"
            :data-section="$headerSection"
        />

        @isset($summaryStrip)
            {{ $summaryStrip }}
        @endisset

        {{ $toolbar }}
        {{ $results }}
    </div>
</x-app-shell>
