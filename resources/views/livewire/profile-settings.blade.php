<x-page-stack data-section="profile-settings">
    <x-page-header
        :eyebrow="__('auth.settings.eyebrow')"
        :title="__('auth.settings.title')"
        :description="__('auth.settings.description')"
        heading-id="profile-settings-heading"
        :action-label="__('auth.settings.back')"
        action-icon="arrow-left"
        :action-href="$this->profileUrl"
    />

    <div class="grid min-w-0 gap-5 lg:grid-cols-[minmax(0,2fr)_minmax(17rem,1fr)] lg:items-start">
        <x-content-panel
            eyebrow="{{ __('auth.settings.preferences_eyebrow') }}"
            title="{{ __('auth.settings.preferences_title') }}"
        >
            <p class="mt-4 text-sm leading-6 text-paw-muted">
                {{ __('auth.settings.preferences_description') }}
            </p>

            <form wire:submit="save" class="mt-5 grid gap-5">
                @if ($errors->any())
                    <div role="alert" tabindex="-1" class="rounded-lg border border-paw-coral/40 bg-paw-coral/10 px-4 py-3 text-sm font-semibold text-paw-ink">
                        {{ __('auth.settings.validation_summary') }}
                    </div>
                @endif

                @if ($feedback !== '')
                    <div role="status" class="rounded-lg border border-paw-leaf/35 bg-paw-leaf/10 px-4 py-3 text-sm font-semibold text-paw-ink">
                        {{ $feedback }}
                    </div>
                @endif

                <div>
                    <label for="profile-settings-locale" class="block text-sm font-semibold text-paw-ink">
                        {{ __('auth.fields.locale') }}
                    </label>
                    <select
                        id="profile-settings-locale"
                        wire:model="form.locale"
                        required
                        aria-describedby="profile-settings-locale-help profile-settings-locale-error"
                        class="mt-2 block min-h-11 w-full rounded-md border border-paw-line bg-white px-3 py-2 text-base focus:border-paw-leaf focus:outline-none focus:ring-2 focus:ring-paw-leaf/25"
                    >
                        @forelse ($this->localeOptions as $value => $label)
                            <option wire:key="profile-locale-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="" disabled>{{ __('auth.settings.no_locales') }}</option>
                        @endforelse
                    </select>
                    <p id="profile-settings-locale-help" class="mt-2 text-sm leading-6 text-paw-muted">
                        {{ __('auth.settings.locale_help') }}
                    </p>
                    @error('form.locale')
                        <p id="profile-settings-locale-error" role="alert" class="mt-2 text-sm font-medium text-paw-coral">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="profile-settings-timezone" class="block text-sm font-semibold text-paw-ink">
                        {{ __('auth.fields.timezone') }}
                    </label>
                    <select
                        id="profile-settings-timezone"
                        wire:model="form.timezone"
                        required
                        aria-describedby="profile-settings-timezone-help profile-settings-timezone-error"
                        class="mt-2 block min-h-11 w-full rounded-md border border-paw-line bg-white px-3 py-2 text-base focus:border-paw-leaf focus:outline-none focus:ring-2 focus:ring-paw-leaf/25"
                    >
                        @forelse ($this->timezoneOptions as $value => $label)
                            <option wire:key="profile-timezone-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="" disabled>{{ __('auth.settings.no_timezones') }}</option>
                        @endforelse
                    </select>
                    <p id="profile-settings-timezone-help" class="mt-2 text-sm leading-6 text-paw-muted">
                        {{ __('auth.settings.timezone_help') }}
                    </p>
                    @error('form.timezone')
                        <p id="profile-settings-timezone-error" role="alert" class="mt-2 text-sm font-medium text-paw-coral">{{ $message }}</p>
                    @enderror
                </div>

                <p wire:dirty role="status" class="text-sm font-medium text-paw-muted">
                    {{ __('auth.form.unsaved') }}
                </p>

                <p wire:offline role="status" class="text-sm font-medium text-paw-coral">
                    {{ __('auth.connection.offline') }}
                </p>

                <div>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md bg-paw-leaf px-5 py-2.5 font-semibold text-white hover:bg-paw-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-paw-leaf focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-60"
                    >
                        <x-ui-icon name="save" size="sm" />
                        <span wire:loading.remove wire:target="save">{{ __('auth.settings.save') }}</span>
                        <span wire:loading wire:target="save">{{ __('auth.settings.saving') }}</span>
                    </button>
                </div>
            </form>
        </x-content-panel>

        <x-content-panel
            eyebrow="{{ __('auth.settings.privacy_eyebrow') }}"
            title="{{ __('auth.settings.privacy_title') }}"
        >
            <p class="mt-4 text-sm leading-6 text-paw-muted">
                {{ __('auth.settings.privacy_description') }}
            </p>
        </x-content-panel>
    </div>
</x-page-stack>
