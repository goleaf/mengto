<div>
    <header class="mb-6">
        <h1 class="text-2xl font-semibold">{{ __('auth.login.title') }}</h1>
        <p class="mt-2 text-sm leading-6 text-paw-muted">{{ __('auth.login.description') }}</p>
    </header>

    @if (session('feedback'))
        <p role="status" class="mb-5 rounded-md border border-paw-leaf bg-paw-mint px-4 py-3 text-sm">
            {{ session('feedback') }}
        </p>
    @endif

    <form
        wire:submit="authenticate"
        x-on:auth-validation-failed.window="$nextTick(() => $refs.email.focus())"
        class="space-y-5"
    >
        <div>
            <label for="login-email" class="block text-sm font-medium">{{ __('auth.fields.email') }}</label>
            <input
                id="login-email"
                type="email"
                wire:model="form.email"
                autocomplete="email"
                required
                autofocus
                x-ref="email"
                aria-describedby="login-email-error"
                class="mt-2 block min-h-11 w-full rounded-md border border-paw-line bg-white px-3 py-2 text-base focus:border-paw-leaf focus:outline-none focus:ring-2 focus:ring-paw-leaf/25"
            >
            @error('form.email')
                <p id="login-email-error" role="alert" class="mt-2 text-sm text-paw-coral">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="flex items-center justify-between gap-4">
                <label for="login-password" class="block text-sm font-medium">{{ __('auth.fields.password') }}</label>
                <a href="{{ route('password.request') }}" wire:navigate class="text-sm font-medium text-paw-leaf underline-offset-4 hover:underline">
                    {{ __('auth.login.forgot_password') }}
                </a>
            </div>
            <input
                id="login-password"
                type="password"
                wire:model="form.password"
                autocomplete="current-password"
                required
                aria-describedby="login-password-error"
                class="mt-2 block min-h-11 w-full rounded-md border border-paw-line bg-white px-3 py-2 text-base focus:border-paw-leaf focus:outline-none focus:ring-2 focus:ring-paw-leaf/25"
            >
            @error('form.password')
                <p id="login-password-error" role="alert" class="mt-2 text-sm text-paw-coral">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex min-h-11 items-center gap-3 text-sm">
            <input type="checkbox" wire:model="form.remember" class="size-4 rounded border-paw-line text-paw-leaf focus:ring-paw-leaf">
            <span>{{ __('auth.login.remember') }}</span>
        </label>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="authenticate"
            class="inline-flex min-h-11 w-full items-center justify-center rounded-md bg-paw-leaf px-4 py-2 font-semibold text-white hover:bg-paw-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-paw-leaf focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-60"
        >
            <span wire:loading.remove wire:target="authenticate">{{ __('auth.login.submit') }}</span>
            <span wire:loading wire:target="authenticate">{{ __('auth.login.submitting') }}</span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-paw-muted">
        {{ __('auth.login.no_account') }}
        <a href="{{ route('register') }}" wire:navigate class="font-semibold text-paw-leaf underline-offset-4 hover:underline">
            {{ __('auth.login.register') }}
        </a>
    </p>
</div>
