<div data-auth-page="reset-password">
    <x-auth-page-header
        eyebrow="{{ __('auth.password.eyebrow') }}"
        title="{{ __('auth.password.reset_title') }}"
        description="{{ __('auth.password.reset_description') }}"
    />

    <form
        method="POST"
        action="{{ route('password.reset', ['token' => $token]) }}"
        wire:submit="resetPassword"
        class="auth-form"
    >
        @csrf
        <x-auth-field
            id="reset-email"
            label="{{ __('auth.fields.email') }}"
            error="form.email"
            type="email"
            autocomplete="email"
            wire:model="form.email"
            autofocus
        />

        <x-auth-field
            id="reset-password"
            label="{{ __('auth.fields.password') }}"
            error="form.password"
            type="password"
            autocomplete="new-password"
            :help="__('auth.register.password_help')"
            wire:model="form.password"
        />

        <x-auth-field
            id="reset-password-confirmation"
            label="{{ __('auth.fields.password_confirmation') }}"
            error="form.password_confirmation"
            type="password"
            autocomplete="new-password"
            wire:model="form.password_confirmation"
        />

        <p wire:dirty role="status" class="auth-form-state">
            {{ __('auth.form.unsaved') }}
        </p>

        <x-auth-submit
            target="resetPassword"
            label="{{ __('auth.password.reset_submit') }}"
            loading-label="{{ __('auth.password.resetting') }}"
        />
    </form>
</div>
