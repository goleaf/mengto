<div data-auth-page="forgot-password">
    <x-auth-page-header
        eyebrow="{{ __('auth.password.eyebrow') }}"
        title="{{ __('auth.password.forgot_title') }}"
        description="{{ __('auth.password.forgot_description') }}"
    />

    @if ($sent)
        <x-auth-status role="status" class="mb-5">
            {{ __('auth.password.link_sent') }}
        </x-auth-status>
    @endif

    <form wire:submit="sendResetLink" class="auth-form">
        <x-auth-field
            id="forgot-email"
            label="{{ __('auth.fields.email') }}"
            error="email"
            type="email"
            autocomplete="email"
            wire:model="email"
            autofocus
        />

        <x-auth-submit
            target="sendResetLink"
            label="{{ __('auth.password.send_link') }}"
            loading-label="{{ __('auth.password.sending') }}"
        />
    </form>

    <a href="{{ route('login') }}" class="auth-back-link">
        <x-lucide-arrow-left aria-hidden="true" />
        <span>{{ __('auth.password.back_to_login') }}</span>
    </a>
</div>
