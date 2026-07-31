<div data-auth-page="confirm-password">
    <x-auth-page-header
        eyebrow="{{ __('auth.confirm_password.eyebrow') }}"
        title="{{ __('auth.confirm_password.title') }}"
        description="{{ __('auth.confirm_password.description') }}"
    />

    <form
        wire:submit="confirm"
        x-on:auth-validation-failed.window="$nextTick(() => $refs.password.focus())"
        class="auth-form"
    >
        <x-auth-field
            id="confirm-password"
            label="{{ __('auth.fields.password') }}"
            error="form.password"
            type="password"
            autocomplete="current-password"
            wire:model="form.password"
            autofocus
            x-ref="password"
        />

        <x-auth-submit
            target="confirm"
            label="{{ __('auth.confirm_password.submit') }}"
            loading-label="{{ __('auth.confirm_password.submitting') }}"
        />
    </form>
</div>
