@props(['settings'])

<x-content-panel section="notification-settings" title="{{ __('ui.quiet_settings_ce4695a9ba') }}">
    <div class="section-body">
        @forelse ($settings as $setting)
            <x-action-form
                :action="route('actions.perform')"
                :payload="[
                    'action' => 'toggle-setting',
                    'target' => $setting['key'],
                    'label' => $setting['label'],
                ]"
            >
                <button type="submit" class="setting-option" aria-pressed="{{ $setting['enabled'] ? 'true' : 'false' }}">
                    <span class="setting-option__control" aria-hidden="true">
                        @if ($setting['enabled'])
                            <x-ui-icon name="check" size="xs" />
                        @endif
                    </span>
                    <span class="setting-option__content">
                        <span class="setting-option__label">{{ $setting['label'] }}</span>
                        <span class="setting-option__description">{{ $setting['description'] }}</span>
                    </span>
                </button>
            </x-action-form>
        @empty
            <p class="text-sm text-paw-muted">{{ __('ui.notification_settings_unavailable_76d0b29f6f') }}</p>
        @endforelse
    </div>
</x-content-panel>
