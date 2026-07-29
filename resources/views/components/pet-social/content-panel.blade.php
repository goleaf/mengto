@props([
    'section' => null,
    'eyebrow' => null,
    'title',
    'titleId' => null,
    'size' => 'regular',
    'tone' => 'leaf',
    'meta' => null,
])

<x-pet-social.panel :section="$section" {{ $attributes }}>
    @if (! $eyebrow)
        <x-pet-social.panel-heading :title="$title" :meta="$meta">
            @isset($aside)
                <x-slot:aside>{{ $aside }}</x-slot:aside>
            @endisset
        </x-pet-social.panel-heading>
    @elseif (isset($aside) || $meta)
        <x-pet-social.panel-heading :meta="$meta">
            <x-slot:heading>
                <x-pet-social.section-heading
                    :eyebrow="$eyebrow"
                    :title="$title"
                    :title-id="$titleId"
                    :size="$size"
                    :tone="$tone"
                />
            </x-slot:heading>

            @isset($aside)
                <x-slot:aside>{{ $aside }}</x-slot:aside>
            @endisset
        </x-pet-social.panel-heading>
    @else
        <x-pet-social.section-heading
            :eyebrow="$eyebrow"
            :title="$title"
            :title-id="$titleId"
            :size="$size"
            :tone="$tone"
        />
    @endif

    {{ $slot }}
</x-pet-social.panel>
