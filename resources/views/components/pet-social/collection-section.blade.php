@props([
    'section' => null,
    'eyebrow',
    'title',
    'titleId' => null,
    'columns' => 1,
])

<section
    @if ($section) data-section="{{ $section }}" @endif
    @if ($titleId) aria-labelledby="{{ $titleId }}" @endif
    {{ $attributes }}
>
    <div class="flex items-end justify-between gap-4">
        <x-pet-social.section-heading
            :eyebrow="$eyebrow"
            :title="$title"
            :title-id="$titleId"
        />

        @isset($action)
            <div class="shrink-0">{{ $action }}</div>
        @endisset
    </div>

    <div
        role="list"
        @class([
            'mt-4 grid gap-4',
            'xl:grid-cols-2' => $columns === 2,
        ])
    >
        {{ $slot }}
    </div>
</section>
