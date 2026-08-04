<section
    class="messaging-call-stage"
    data-call-stage
    data-device-unavailable="{{ __('messaging.call_stage.device.unavailable') }}"
    data-camera-active="{{ __('messaging.call_stage.device.camera_active') }}"
    data-microphone-active="{{ __('messaging.call_stage.device.microphone_active') }}"
    data-device-denied="{{ __('messaging.call_stage.device.permission_denied') }}"
    @if ($call)
        data-call-type-code="{{ $call['type_code'] }}"
        data-call-status-code="{{ $call['status_code'] }}"
        data-call-quality-code="{{ $call['quality_code'] }}"
    @endif
    @if (! $call) hidden @endif
    aria-labelledby="call-stage-title"
>
    <div class="messaging-call-stage__backdrop" aria-hidden="true"></div>
    <div class="messaging-call-stage__dialog" role="dialog" aria-modal="true">
        @if ($call)
            <header>
                <div>
                    <p data-call-status-line>{{ __('messaging.call_stage.status_line', ['type' => $call['type_label'], 'status' => $call['status_label']]) }}</p>
                    <h2 id="call-stage-title">{{ $conversation['name'] }}</h2>
                    <span data-call-quality>{{ $conversation['pet'] }} · {{ $call['quality'] }}</span>
                </div>
                <form method="POST" action="{{ route('messages.actions') }}" data-call-end>
                    @csrf
                    <input type="hidden" name="action" value="end-message-call">
                    <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                    <input type="hidden" name="return_filter" value="{{ $activeFilter }}">
                    <button type="submit" class="messaging-icon-button" title="{{ __('messaging.call_stage.actions.close') }}">
                        <x-ui-icon name="x" />
                        <span class="sr-only">{{ __('messaging.call_stage.actions.close') }}</span>
                    </button>
                </form>
            </header>

            <div class="messaging-call-stage__preview">
                <video data-call-preview muted playsinline hidden></video>
                <div data-call-placeholder>
                    <img src="{{ $conversation['avatar'] }}" alt="" width="112" height="112">
                    <strong>{{ $conversation['name'] }}</strong>
                    <span data-call-device-status>{{ __('messaging.call_stage.device.not_requested') }}</span>
                </div>
                <span class="messaging-call-stage__recording">
                    <x-ui-icon name="circle-dot" size="sm" />
                    {{ __('messaging.call_stage.recording_off') }}
                </span>
            </div>

            <div class="messaging-call-stage__checks">
                <button type="button" data-call-device="microphone"><x-ui-icon name="mic" size="sm" /> {{ __('messaging.call_stage.checks.test_microphone') }}</button>
                @if ($call['type_code'] === 'video')
                    <button type="button" data-call-device="camera"><x-ui-icon name="camera" size="sm" /> {{ __('messaging.call_stage.checks.preview_camera') }}</button>
                @endif
                <span><x-ui-icon name="wifi" size="sm" /> {{ __('messaging.call_stage.checks.browser_connection') }}</span>
            </div>

            <div class="messaging-call-stage__notice">
                <x-ui-icon name="shield-check" />
                <p>
                    <strong>{{ __('messaging.call_stage.consent_title') }}</strong>
                    <span>
                        <span data-call-boundary-transport>{{ $boundary['transport'] }}</span>
                        <span data-call-boundary-recording>{{ $boundary['recording'] }}</span>
                    </span>
                </p>
            </div>

            <div class="messaging-call-stage__controls" aria-label="{{ __('messaging.call_stage.label') }}">
                @forelse ($controls as $control)
                    <form method="POST" action="{{ route('messages.actions') }}">
                        @csrf
                        <input type="hidden" name="action" value="update-message-call">
                        <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                        <input type="hidden" name="call_control" value="{{ $control['control'] }}">
                        <input type="hidden" name="return_filter" value="{{ $activeFilter }}">
                        <button type="submit">
                            <x-ui-icon :name="$control['icon']" />
                            <span>{{ $control['label'] }}</span>
                        </button>
                    </form>
                @empty
                @endforelse
            </div>

            <footer>
                <p data-call-boundary-emergency><x-ui-icon name="triangle-alert" size="sm" /> {{ $boundary['emergency'] }}</p>
                @if ($call['status_code'] === 'preflight')
                    <form method="POST" action="{{ route('messages.actions') }}">
                        @csrf
                        <input type="hidden" name="action" value="update-message-call">
                        <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                        <input type="hidden" name="call_control" value="join">
                        <input type="hidden" name="return_filter" value="{{ $activeFilter }}">
                        <button type="submit" class="action action--primary action--regular">
                            <x-ui-icon name="phone-call" size="sm" />
                            <span>{{ __('messaging.call_stage.actions.join') }}</span>
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('messages.actions') }}">
                        @csrf
                        <input type="hidden" name="action" value="end-message-call">
                        <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                        <input type="hidden" name="return_filter" value="{{ $activeFilter }}">
                        <button type="submit" class="action action--danger action--regular">
                            <x-ui-icon name="phone-off" size="sm" />
                            <span>{{ __('messaging.call_stage.actions.end') }}</span>
                        </button>
                    </form>
                @endif
            </footer>
        @endif
    </div>
</section>
