<div data-auth-page="verify-email">
    <x-auth-page-header
        eyebrow="{{ __('auth.verification.eyebrow') }}"
        title="{{ __('auth.verification.title') }}"
        description="{{ __('auth.verification.description') }}"
    />

    @if (session('verification_delivery_failed'))
        <x-auth-status role="alert" tone="danger" class="mb-5">
            {{ __('auth.verification.delivery_failed') }}
        </x-auth-status>
    @endif

    @if ($sent)
        <x-auth-status role="status" class="mb-5">
            {{ __('auth.verification.sent') }}
        </x-auth-status>
    @endif

    @error('resend')
        <x-auth-status role="alert" tone="danger" class="mb-5">
            {{ $message }}
        </x-auth-status>
    @enderror

    <div class="auth-actions">
        <x-auth-submit
            type="button"
            target="resend"
            label="{{ __('auth.verification.resend') }}"
            loading-label="{{ __('auth.verification.sending') }}"
            wire:click="resend"
        />

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="auth-button auth-button--secondary">
                <x-ui-icon name="log-out" size="sm" />
                <span>{{ __('auth.logout') }}</span>
            </button>
        </form>
    </div>
</div>
