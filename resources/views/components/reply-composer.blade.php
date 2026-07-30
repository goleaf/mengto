@props([
    'action',
    'target',
    'label',
    'placeholder',
    'fieldId',
    'submitLabel' => __('ui.send_f6f4688ff2'),
    'submitIcon' => 'send',
    'variant' => 'embedded',
])

<form
    method="POST"
    action="{{ route('actions.perform') }}"
    {{ $attributes->class([
        'reply-composer',
        'reply-composer--'.$variant,
    ]) }}
>
    @csrf
    <input type="hidden" name="action" value="{{ $action }}">
    <input type="hidden" name="target" value="{{ $target }}">
    <input type="hidden" name="label" value="{{ $label }}">

    <label for="{{ $fieldId }}" class="sr-only">{{ $label }}</label>
    <textarea
        id="{{ $fieldId }}"
        name="body"
        rows="3"
        required
        placeholder="{{ $placeholder }}"
        @if ($errors->has('body')) aria-invalid="true" aria-describedby="{{ $fieldId }}-error" @endif
        class="field field--textarea"
    >{{ old('body') }}</textarea>

    @if ($errors->has('body'))
        <p id="{{ $fieldId }}-error" class="form-field__error mt-2">{{ $errors->first('body') }}</p>
    @endif

    <x-action-group class="mt-3">
        <x-action-control
            type="submit"
            :label="$submitLabel"
            :icon="$submitIcon"
            variant="primary"
            size="toolbar"
        />
    </x-action-group>
</form>
