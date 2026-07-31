<div data-auth-page="verify-email">
    <x-auth-page-header
        eyebrow="{{ __('auth.verification.eyebrow') }}"
        title="{{ __('auth.verification.title') }}"
        description="{{ __('auth.verification.description') }}"
    />

    @if ($sent)
        <x-auth-status role="status" class="mb-5">
            {{ __('auth.verification.sent') }}
        </x-auth-status>
    @endif

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
                {{ __('auth.logout') }}
            </button>
        </form>
    </div>
</div>
