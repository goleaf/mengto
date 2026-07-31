@props([
    'id',
    'label',
    'error',
    'type' => 'text',
    'autocomplete' => null,
    'help' => null,
    'required' => true,
    'autofocus' => false,
])

<div class="auth-field">
    <div class="auth-field__heading">
        <label for="{{ $id }}" class="auth-field__label">
            {{ $label }}
            @if ($required)
                <span class="auth-field__required" aria-hidden="true">*</span>
                <span class="sr-only">{{ __('ui.required_d0a3630555') }}</span>
            @endif
        </label>

        @isset($action)
            <div class="auth-field__action">{{ $action }}</div>
        @endisset
    </div>

    <input
        id="{{ $id }}"
        name="{{ $id }}"
        type="{{ $type }}"
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @required($required)
        @if ($autofocus) autofocus @endif
        @if ($errors->has($error) || $help) aria-describedby="{{ $id }}-feedback" @endif
        @if ($errors->has($error)) aria-invalid="true" @endif
        {{ $attributes->class('auth-field__input') }}
    >

    @if ($errors->has($error) || $help)
        <div id="{{ $id }}-feedback" class="auth-field__feedback">
            @if ($errors->has($error))
                <p role="alert" class="auth-field__error">{{ $errors->first($error) }}</p>
            @else
                <p class="auth-field__help">{{ $help }}</p>
            @endif
        </div>
    @endif
</div>
