<div data-auth-page="login">
    <x-auth-page-header
        eyebrow="{{ __('auth.login.eyebrow') }}"
        title="{{ __('auth.login.title') }}"
        description="{{ __('auth.login.description') }}"
    />

    @if (session('feedback'))
        <x-auth-status role="status" class="mb-5">
            {{ session('feedback') }}
        </x-auth-status>
    @endif

    <form
        wire:submit="authenticate"
        x-on:auth-validation-failed.window="$nextTick(() => $refs.email.focus())"
        class="auth-form"
    >
        <x-auth-field
            id="login-email"
            label="{{ __('auth.fields.email') }}"
            error="form.email"
            type="email"
            autocomplete="email"
            wire:model="form.email"
            autofocus
            x-ref="email"
        />

        <x-auth-field
            id="login-password"
            label="{{ __('auth.fields.password') }}"
            error="form.password"
            type="password"
            autocomplete="current-password"
            wire:model="form.password"
        >
            <x-slot:action>
                <a href="{{ route('password.request') }}" class="auth-text-link">
                    {{ __('auth.login.forgot_password') }}
                </a>
            </x-slot:action>
        </x-auth-field>

        <label class="auth-checkbox">
            <input type="checkbox" wire:model="form.remember" class="auth-checkbox__control">
            <span>{{ __('auth.login.remember') }}</span>
        </label>

        <x-auth-submit
            target="authenticate"
            label="{{ __('auth.login.submit') }}"
            loading-label="{{ __('auth.login.submitting') }}"
        />
    </form>

    <x-auth-switch-link
        :prompt="__('auth.login.no_account')"
        :href="route('register')"
        label="{{ __('auth.login.register') }}"
    />
</div>
