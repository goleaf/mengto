<div>
    <header class="mb-6">
        <h1 class="text-2xl font-semibold">{{ __('auth.password.forgot_title') }}</h1>
        <p class="mt-2 text-sm leading-6 text-paw-muted">{{ __('auth.password.forgot_description') }}</p>
    </header>

    @if ($sent)
        <p role="status" class="mb-5 rounded-md border border-paw-leaf bg-paw-mint px-4 py-3 text-sm">
            {{ __('auth.password.link_sent') }}
        </p>
    @endif

    <form wire:submit="sendResetLink" class="space-y-5">
        <div>
            <label for="forgot-email" class="block text-sm font-medium">{{ __('auth.fields.email') }}</label>
            <input id="forgot-email" type="email" wire:model="email" autocomplete="email" required autofocus aria-describedby="forgot-email-error" class="mt-2 block min-h-11 w-full rounded-md border border-paw-line bg-white px-3 py-2 text-base focus:border-paw-leaf focus:outline-none focus:ring-2 focus:ring-paw-leaf/25">
            @error('email')
                <p id="forgot-email-error" role="alert" class="mt-2 text-sm text-paw-coral">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" wire:loading.attr="disabled" wire:target="sendResetLink" class="inline-flex min-h-11 w-full items-center justify-center rounded-md bg-paw-leaf px-4 py-2 font-semibold text-white hover:bg-paw-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-paw-leaf focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-60">
            <span wire:loading.remove wire:target="sendResetLink">{{ __('auth.password.send_link') }}</span>
            <span wire:loading wire:target="sendResetLink">{{ __('auth.password.sending') }}</span>
        </button>
    </form>

    <a href="{{ route('login') }}" wire:navigate class="mt-6 inline-flex text-sm font-semibold text-paw-leaf underline-offset-4 hover:underline">
        {{ __('auth.password.back_to_login') }}
    </a>
</div>
