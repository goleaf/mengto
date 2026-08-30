<div class="form-field">
    <label for="{{ $name }}" class="form-field__label">
        {{ $field['label'] }}
        @if ($required)
            <span class="form-field__required" aria-hidden="true">*</span>
            <span class="sr-only">{{ __('ui.required_lowercase') }}</span>
        @endif
    </label>

    @if ($field['type'] === 'select')
        <select
            id="{{ $name }}"
            name="{{ $name }}"
            @required($required)
            @if ($errors->has($name)) aria-invalid="true" aria-describedby="{{ $errorId }}" @endif
            class="field field--select"
        >
            @foreach ($field['options'] as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>
    @elseif ($field['type'] === 'textarea')
        <textarea
            id="{{ $name }}"
            name="{{ $name }}"
            rows="5"
            @required($required)
            placeholder="{{ $field['placeholder'] }}"
            @if ($errors->has($name)) aria-invalid="true" aria-describedby="{{ $errorId }}" @endif
            class="field field--textarea"
        >{{ $value }}</textarea>
    @else
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="{{ $field['type'] }}"
            value="{{ $value }}"
            @required($required)
            @if ($field['min'] ?? null) min="{{ $field['min'] }}" @endif
            @if ($field['autocomplete'] ?? null) autocomplete="{{ $field['autocomplete'] }}" @endif
            placeholder="{{ $field['placeholder'] }}"
            @if ($errors->has($name)) aria-invalid="true" aria-describedby="{{ $errorId }}" @endif
            class="field"
        >
    @endif

    @if ($errors->has($name))
        <p id="{{ $errorId }}" class="form-field__error">{{ $errors->first($name) }}</p>
    @endif
</div>
