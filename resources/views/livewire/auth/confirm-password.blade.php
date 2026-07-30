<div>
    <header class="mb-6">
        <h1 class="text-2xl font-semibold">{{ __('auth.confirm_password.title') }}</h1>
        <p class="mt-2 text-sm leading-6 text-paw-muted">{{ __('auth.confirm_password.description') }}</p>
    </header>

    <form
        wire:submit="confirm"
        x-on:auth-validation-failed.window="$nextTick(() => $refs.password.focus())"
        class="space-y-5"
    >
        <div>
            <label for="confirm-password" class="block text-sm font-medium">{{ __('auth.fields.password') }}</label>
            <input
                id="confirm-password"
                type="password"
                wire:model="form.password"
                autocomplete="current-password"
                required
                autofocus
                x-ref="password"
                aria-describedby="confirm-password-error"
                class="mt-2 block min-h-11 w-full rounded-md border border-paw-line bg-white px-3 py-2 text-base focus:border-paw-leaf focus:outline-none focus:ring-2 focus:ring-paw-leaf/25"
            >
            @error('form.password')
                <p id="confirm-password-error" role="alert" class="mt-2 text-sm text-paw-coral">{{ $message }}</p>
            @enderror
        </div>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="confirm"
            class="inline-flex min-h-11 w-full items-center justify-center rounded-md bg-paw-leaf px-4 py-2 font-semibold text-white hover:bg-paw-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-paw-leaf focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-60"
        >
            <span wire:loading.remove wire:target="confirm">{{ __('auth.confirm_password.submit') }}</span>
            <span wire:loading wire:target="confirm">{{ __('auth.confirm_password.submitting') }}</span>
        </button>
    </form>
</div>
