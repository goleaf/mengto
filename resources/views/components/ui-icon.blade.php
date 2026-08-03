@props([
    'name',
    'size' => 'md',
    'label' => null,
])

@if ($label !== null)
    <x-dynamic-component
        :component="'lucide-'.$name"
        data-ui-icon="{{ $name }}"
        role="img"
        aria-label="{{ $label }}"
        {{ $attributes->class([
            'ui-icon',
            'ui-icon--xs' => $size === 'xs',
            'ui-icon--sm' => $size === 'sm',
            'ui-icon--md' => $size === 'md',
            'ui-icon--lg' => $size === 'lg',
            'ui-icon--xl' => $size === 'xl',
            'ui-icon--2xl' => $size === '2xl',
            'ui-icon--3xl' => $size === '3xl',
            'ui-icon--4xl' => $size === '4xl',
            'ui-icon--display' => $size === 'display',
            'ui-icon--hero' => $size === 'hero',
        ]) }}
    />
@else
    <x-dynamic-component
        :component="'lucide-'.$name"
        data-ui-icon="{{ $name }}"
        aria-hidden="true"
        {{ $attributes->class([
            'ui-icon',
            'ui-icon--xs' => $size === 'xs',
            'ui-icon--sm' => $size === 'sm',
            'ui-icon--md' => $size === 'md',
            'ui-icon--lg' => $size === 'lg',
            'ui-icon--xl' => $size === 'xl',
            'ui-icon--2xl' => $size === '2xl',
            'ui-icon--3xl' => $size === '3xl',
            'ui-icon--4xl' => $size === '4xl',
            'ui-icon--display' => $size === 'display',
            'ui-icon--hero' => $size === 'hero',
        ]) }}
    />
@endif
