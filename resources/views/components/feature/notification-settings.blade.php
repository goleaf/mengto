@props(['settings'])

<x-ui.content-panel section="notification-settings" title="Quiet settings">
    <div class="section-body">
        @forelse ($settings as $setting)
            <x-ui.action-form
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
                            <x-lucide-check class="icon icon--xs" />
                        @endif
                    </span>
                    <span class="setting-option__content">
                        <span class="setting-option__label">{{ $setting['label'] }}</span>
                        <span class="setting-option__description">{{ $setting['description'] }}</span>
                    </span>
                </button>
            </x-ui.action-form>
        @empty
            <p class="text-sm text-paw-muted">Notification settings unavailable.</p>
        @endforelse
    </div>
</x-ui.content-panel>
