<div>
    <header class="mb-6">
        <h1 class="text-2xl font-semibold">{{ __('auth.verification.title') }}</h1>
        <p class="mt-2 text-sm leading-6 text-paw-muted">{{ __('auth.verification.description') }}</p>
    </header>

    @if ($sent)
        <p role="status" class="mb-5 rounded-md border border-paw-leaf bg-paw-mint px-4 py-3 text-sm">
            {{ __('auth.verification.sent') }}
        </p>
    @endif

    <div class="space-y-4">
        <button type="button" wire:click="resend" wire:loading.attr="disabled" wire:target="resend" class="inline-flex min-h-11 w-full items-center justify-center rounded-md bg-paw-leaf px-4 py-2 font-semibold text-white hover:bg-paw-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-paw-leaf focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-60">
            <span wire:loading.remove wire:target="resend">{{ __('auth.verification.resend') }}</span>
            <span wire:loading wire:target="resend">{{ __('auth.verification.sending') }}</span>
        </button>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-md border border-paw-line bg-white px-4 py-2 font-semibold text-paw-ink hover:bg-paw-cream focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-paw-leaf focus-visible:ring-offset-2">
                {{ __('auth.logout') }}
            </button>
        </form>
    </div>
</div>
