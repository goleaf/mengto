<div>
    <header class="mb-6">
        <h1 class="text-2xl font-semibold">{{ __('auth.password.reset_title') }}</h1>
        <p class="mt-2 text-sm leading-6 text-paw-muted">{{ __('auth.password.reset_description') }}</p>
    </header>

    <form wire:submit="resetPassword" class="space-y-5">
        <div>
            <label for="reset-email" class="block text-sm font-medium">{{ __('auth.fields.email') }}</label>
            <input id="reset-email" type="email" wire:model="form.email" autocomplete="email" required autofocus aria-describedby="reset-email-error" class="mt-2 block min-h-11 w-full rounded-md border border-paw-line bg-white px-3 py-2 text-base focus:border-paw-leaf focus:outline-none focus:ring-2 focus:ring-paw-leaf/25">
            @error('form.email')
                <p id="reset-email-error" role="alert" class="mt-2 text-sm text-paw-coral">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="reset-password" class="block text-sm font-medium">{{ __('auth.fields.password') }}</label>
            <input id="reset-password" type="password" wire:model="form.password" autocomplete="new-password" required aria-describedby="reset-password-error" class="mt-2 block min-h-11 w-full rounded-md border border-paw-line bg-white px-3 py-2 text-base focus:border-paw-leaf focus:outline-none focus:ring-2 focus:ring-paw-leaf/25">
            @error('form.password')
                <p id="reset-password-error" role="alert" class="mt-2 text-sm text-paw-coral">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="reset-password-confirmation" class="block text-sm font-medium">{{ __('auth.fields.password_confirmation') }}</label>
            <input id="reset-password-confirmation" type="password" wire:model="form.password_confirmation" autocomplete="new-password" required class="mt-2 block min-h-11 w-full rounded-md border border-paw-line bg-white px-3 py-2 text-base focus:border-paw-leaf focus:outline-none focus:ring-2 focus:ring-paw-leaf/25">
        </div>

        <p wire:dirty role="status" class="text-sm font-medium text-paw-muted">
            {{ __('auth.form.unsaved') }}
        </p>

        <button type="submit" wire:loading.attr="disabled" wire:target="resetPassword" class="inline-flex min-h-11 w-full items-center justify-center rounded-md bg-paw-leaf px-4 py-2 font-semibold text-white hover:bg-paw-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-paw-leaf focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-60">
            <span wire:loading.remove wire:target="resetPassword">{{ __('auth.password.reset_submit') }}</span>
            <span wire:loading wire:target="resetPassword">{{ __('auth.password.resetting') }}</span>
        </button>
    </form>
</div>
