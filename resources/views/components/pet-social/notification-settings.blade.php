@props(['settings'])

<x-pet-social.content-panel section="notification-settings" title="Quiet settings">
    <div class="pc-section-body">
        @forelse ($settings as $setting)
            <label class="pc-setting-option">
                <input
                    type="checkbox"
                    @checked($setting['enabled'])
                    aria-disabled="true"
                    disabled
                    class="pc-setting-option__control"
                >
                <span class="pc-setting-option__content">
                    <span class="pc-setting-option__label">{{ $setting['label'] }}</span>
                    <span class="pc-setting-option__description">{{ $setting['description'] }}</span>
                </span>
            </label>
        @empty
            <p class="text-sm text-paw-muted">Notification settings unavailable.</p>
        @endforelse
    </div>
</x-pet-social.content-panel>
