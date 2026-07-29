@props([
    'owner',
    'title',
    'activeSection',
    'summary',
    'headerSection',
    'actionLabel' => null,
    'actionIcon' => null,
])

<x-pet-social.app-shell :owner="$owner" :title="$title" :active-section="$activeSection">
    <div {{ $attributes->class(['grid gap-5']) }}>
        <x-pet-social.page-header
            :eyebrow="$summary['eyebrow']"
            :title="$summary['title']"
            :description="$summary['description']"
            :count="$summary['count']"
            :action-label="$actionLabel"
            :action-icon="$actionIcon"
            :data-section="$headerSection"
        />

        @isset($summaryStrip)
            {{ $summaryStrip }}
        @endisset

        {{ $toolbar }}
        {{ $results }}
    </div>
</x-pet-social.app-shell>
