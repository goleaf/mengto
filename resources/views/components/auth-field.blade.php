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
                <span class="sr-only">{{ __('ui.required_lowercase') }}</span>
            @endif
        </label>

        @isset($action)
            <div class="auth-field__action">{{ $action }}</div>
        @endisset
    </div>

    <div
        @class([
            'auth-field__control',
            'auth-field__control--password' => $type === 'password',
        ])
        @if ($type === 'password')
            x-data="{ passwordVisible: false }"
            data-password-visibility="{{ $id }}"
        @endif
    >
        <input
            id="{{ $id }}"
            name="{{ $id }}"
            type="{{ $type }}"
            @if ($type === 'password') x-bind:type="passwordVisible ? 'text' : 'password'" @endif
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @required($required)
            @if ($autofocus) autofocus @endif
            @if ($errors->has($error) || $help) aria-describedby="{{ $id }}-feedback" @endif
            @if ($errors->has($error)) aria-invalid="true" @endif
            {{ $attributes->class('auth-field__input') }}
        >

        @if ($type === 'password')
            <button
                type="button"
                class="auth-field__password-toggle"
                aria-controls="{{ $id }}"
                aria-label="{{ __('auth.password_visibility.show') }}"
                aria-pressed="false"
                data-show-label="{{ __('auth.password_visibility.show') }}"
                data-hide-label="{{ __('auth.password_visibility.hide') }}"
                x-on:click="passwordVisible = ! passwordVisible"
                x-bind:aria-label="passwordVisible ? $el.dataset.hideLabel : $el.dataset.showLabel"
                x-bind:aria-pressed="passwordVisible ? 'true' : 'false'"
            >
                <span class="auth-field__password-icon" x-show="! passwordVisible">
                    <x-ui-icon name="eye" size="sm" />
                </span>
                <span class="auth-field__password-icon" x-cloak x-show="passwordVisible">
                    <x-ui-icon name="eye-off" size="sm" />
                </span>
            </button>
        @endif
    </div>

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
