<div data-auth-page="register">
    <x-auth-page-header
        eyebrow="{{ __('auth.register.eyebrow') }}"
        title="{{ __('auth.register.title') }}"
        description="{{ __('auth.register.description') }}"
    />

    <form method="POST" action="{{ route('register') }}" wire:submit="register" class="auth-form">
        @csrf
        <x-auth-field
            id="register-name"
            label="{{ __('auth.fields.name') }}"
            error="form.name"
            autocomplete="name"
            wire:model="form.name"
            autofocus
        />

        <x-auth-field
            id="register-email"
            label="{{ __('auth.fields.email') }}"
            error="form.email"
            type="email"
            autocomplete="email"
            wire:model="form.email"
        />

        <x-auth-field
            id="register-password"
            label="{{ __('auth.fields.password') }}"
            error="form.password"
            type="password"
            autocomplete="new-password"
            :help="__('auth.register.password_help')"
            wire:model="form.password"
        />

        <x-auth-field
            id="register-password-confirmation"
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
            target="register"
            label="{{ __('auth.register.submit') }}"
            loading-label="{{ __('auth.register.submitting') }}"
        />
    </form>

    <x-auth-switch-link
        :prompt="__('auth.register.has_account')"
        :href="route('login')"
        label="{{ __('auth.register.login') }}"
    />
</div>
