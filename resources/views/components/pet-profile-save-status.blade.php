@props([
    'feedback' => '',
    'target' => 'autoSaveStep',
])

<div
    role="status"
    aria-live="polite"
    aria-atomic="true"
    data-pet-autosave-status
    {{ $attributes->class(['min-h-6 text-sm text-paw-muted']) }}
>
    <p wire:loading wire:target="{{ $target }}">
        {{ __('pet_profiles.actions.saving') }}
    </p>
    <p wire:loading.remove wire:target="{{ $target }}" wire:dirty>
        {{ __('pet_profiles.feedback.unsaved') }}
    </p>
    @if (! $errors->any())
        <p wire:loading.remove wire:target="{{ $target }}" wire:dirty.remove>
            {{ $feedback !== '' ? $feedback : __('pet_profiles.completion.states.saved') }}
        </p>
    @endif
</div>
