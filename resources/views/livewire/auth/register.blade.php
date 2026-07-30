<div>
    <header class="mb-6">
        <h1 class="text-2xl font-semibold">{{ __('auth.register.title') }}</h1>
        <p class="mt-2 text-sm leading-6 text-paw-muted">{{ __('auth.register.description') }}</p>
    </header>

    <form wire:submit="register" class="space-y-5">
        <div>
            <label for="register-name" class="block text-sm font-medium">{{ __('auth.fields.name') }}</label>
            <input id="register-name" wire:model="form.name" autocomplete="name" required autofocus aria-describedby="register-name-error" class="mt-2 block min-h-11 w-full rounded-md border border-paw-line bg-white px-3 py-2 text-base focus:border-paw-leaf focus:outline-none focus:ring-2 focus:ring-paw-leaf/25">
            @error('form.name')
                <p id="register-name-error" role="alert" class="mt-2 text-sm text-paw-coral">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="register-email" class="block text-sm font-medium">{{ __('auth.fields.email') }}</label>
            <input id="register-email" type="email" wire:model="form.email" autocomplete="email" required aria-describedby="register-email-error" class="mt-2 block min-h-11 w-full rounded-md border border-paw-line bg-white px-3 py-2 text-base focus:border-paw-leaf focus:outline-none focus:ring-2 focus:ring-paw-leaf/25">
            @error('form.email')
                <p id="register-email-error" role="alert" class="mt-2 text-sm text-paw-coral">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="register-locale" class="block text-sm font-medium">{{ __('auth.fields.locale') }}</label>
                <select id="register-locale" wire:model="form.locale" required class="mt-2 block min-h-11 w-full rounded-md border border-paw-line bg-white px-3 py-2 text-base focus:border-paw-leaf focus:outline-none focus:ring-2 focus:ring-paw-leaf/25">
                    @foreach ($locales as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="register-timezone" class="block text-sm font-medium">{{ __('auth.fields.timezone') }}</label>
                <input id="register-timezone" wire:model="form.timezone" autocomplete="off" required aria-describedby="register-timezone-help register-timezone-error" class="mt-2 block min-h-11 w-full rounded-md border border-paw-line bg-white px-3 py-2 text-base focus:border-paw-leaf focus:outline-none focus:ring-2 focus:ring-paw-leaf/25">
                <p id="register-timezone-help" class="mt-2 text-xs text-paw-muted">{{ __('auth.register.timezone_help') }}</p>
                @error('form.timezone')
                    <p id="register-timezone-error" role="alert" class="mt-2 text-sm text-paw-coral">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="register-password" class="block text-sm font-medium">{{ __('auth.fields.password') }}</label>
            <input id="register-password" type="password" wire:model="form.password" autocomplete="new-password" required aria-describedby="register-password-help register-password-error" class="mt-2 block min-h-11 w-full rounded-md border border-paw-line bg-white px-3 py-2 text-base focus:border-paw-leaf focus:outline-none focus:ring-2 focus:ring-paw-leaf/25">
            <p id="register-password-help" class="mt-2 text-xs text-paw-muted">{{ __('auth.register.password_help') }}</p>
            @error('form.password')
                <p id="register-password-error" role="alert" class="mt-2 text-sm text-paw-coral">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="register-password-confirmation" class="block text-sm font-medium">{{ __('auth.fields.password_confirmation') }}</label>
            <input id="register-password-confirmation" type="password" wire:model="form.password_confirmation" autocomplete="new-password" required class="mt-2 block min-h-11 w-full rounded-md border border-paw-line bg-white px-3 py-2 text-base focus:border-paw-leaf focus:outline-none focus:ring-2 focus:ring-paw-leaf/25">
        </div>

        <p wire:dirty role="status" class="text-sm font-medium text-paw-muted">
            {{ __('auth.form.unsaved') }}
        </p>

        <button type="submit" wire:loading.attr="disabled" wire:target="register" class="inline-flex min-h-11 w-full items-center justify-center rounded-md bg-paw-leaf px-4 py-2 font-semibold text-white hover:bg-paw-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-paw-leaf focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-60">
            <span wire:loading.remove wire:target="register">{{ __('auth.register.submit') }}</span>
            <span wire:loading wire:target="register">{{ __('auth.register.submitting') }}</span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-paw-muted">
        {{ __('auth.register.has_account') }}
        <a href="{{ route('login') }}" wire:navigate class="font-semibold text-paw-leaf underline-offset-4 hover:underline">{{ __('auth.register.login') }}</a>
    </p>
</div>
