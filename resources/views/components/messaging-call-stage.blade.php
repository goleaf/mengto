@props(['conversation', 'call', 'boundary', 'activeFilter'])

<section
    class="messaging-call-stage"
    data-call-stage
    data-device-unavailable="{{ __('ui.device_preview_unavailable_3d1620e87e') }}"
    data-camera-active="{{ __('ui.camera_microphone_preview_active_2ef080de44') }}"
    data-microphone-active="{{ __('ui.microphone_preview_active_e303793d16') }}"
    data-device-denied="{{ __('ui.device_permission_denied_9f34b792d9') }}"
    @if (! $call) hidden @endif
    aria-labelledby="call-stage-title"
>
    <div class="messaging-call-stage__backdrop" aria-hidden="true"></div>
    <div class="messaging-call-stage__dialog" role="dialog" aria-modal="true">
        @if ($call)
            <header>
                <div>
                    <p>{{ __('presentation.call_status', ['type' => $call['type_label'], 'status' => $call['status_label']]) }}</p>
                    <h2 id="call-stage-title">{{ $conversation['name'] }}</h2>
                    <span>{{ $conversation['pet'] }} · {{ $call['quality'] }}</span>
                </div>
                <form method="POST" action="{{ route('messages.actions') }}" data-call-end>
                    @csrf
                    <input type="hidden" name="action" value="end-message-call">
                    <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                    <input type="hidden" name="return_filter" value="{{ $activeFilter }}">
                    <button type="submit" class="messaging-icon-button" title="{{ __('ui.close_call_fa0a8b7210') }}">
                        <x-lucide-x class="icon" aria-hidden="true" />
                        <span class="sr-only">{{ __('ui.close_call_fa0a8b7210') }}</span>
                    </button>
                </form>
            </header>

            <div class="messaging-call-stage__preview">
                <video data-call-preview muted playsinline hidden></video>
                <div data-call-placeholder>
                    <img src="{{ $conversation['avatar'] }}" alt="" width="112" height="112">
                    <strong>{{ $conversation['name'] }}</strong>
                    <span data-call-device-status>{{ __('ui.camera_and_microphone_have_not_been_requested_b5b83b9afa') }}</span>
                </div>
                <span class="messaging-call-stage__recording">
                    <x-lucide-circle-dot class="icon icon--sm" aria-hidden="true" />
                    {{ __('ui.recording_off_e03d424d1a') }}
                </span>
            </div>

            <div class="messaging-call-stage__checks">
                <button type="button" data-call-device="microphone"><x-lucide-mic class="icon icon--sm" /> {{ __('ui.test_microphone_4875bbe105') }}</button>
                @if ($call['type'] === 'video')
                    <button type="button" data-call-device="camera"><x-lucide-camera class="icon icon--sm" /> {{ __('ui.preview_camera_aa714af315') }}</button>
                @endif
                <span><x-lucide-wifi class="icon icon--sm" /> {{ __('ui.browser_connection_check_0d0350149d') }}</span>
            </div>

            <div class="messaging-call-stage__notice">
                <x-lucide-shield-check class="icon" aria-hidden="true" />
                <p><strong>{{ __('ui.consent_before_connection_903741cc3f') }}</strong><span>{{ $boundary['transport'] }} {{ $boundary['recording'] }}</span></p>
            </div>

            <div class="messaging-call-stage__controls">
                @foreach ([
                    ['control' => 'microphone', 'icon' => $call['microphone'] ? 'mic' : 'mic-off', 'label' => $call['microphone'] ? __('ui.mute_8dd6857baf') : __('ui.unmute_ce4ee4efc5')],
                    ['control' => 'camera', 'icon' => $call['camera'] ? 'video' : 'video-off', 'label' => $call['camera'] ? __('ui.camera_off_ce3ef7450f') : __('ui.camera_on_071a189a5d')],
                    ['control' => 'captions', 'icon' => 'captions', 'label' => $call['captions'] ? __('ui.captions_off_aca3eb08cd') : __('ui.captions_43bf6af8e2')],
                    ['control' => 'audio-only', 'icon' => 'phone', 'label' => __('ui.audio_only_224b45b631')],
                ] as $control)
                    <form method="POST" action="{{ route('messages.actions') }}" data-call-end>
                        @csrf
                        <input type="hidden" name="action" value="update-message-call">
                        <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                        <input type="hidden" name="call_control" value="{{ $control['control'] }}">
                        <input type="hidden" name="return_filter" value="{{ $activeFilter }}">
                        <button type="submit">
                            <x-dynamic-component :component="'lucide-'.$control['icon']" class="icon" aria-hidden="true" />
                            <span>{{ $control['label'] }}</span>
                        </button>
                    </form>
                @endforeach
            </div>

            <footer>
                <p><x-lucide-triangle-alert class="icon icon--sm" /> {{ $boundary['emergency'] }}</p>
                @if ($call['status'] === 'preflight')
                    <form method="POST" action="{{ route('messages.actions') }}">
                        @csrf
                        <input type="hidden" name="action" value="update-message-call">
                        <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                        <input type="hidden" name="call_control" value="join">
                        <input type="hidden" name="return_filter" value="{{ $activeFilter }}">
                        <button type="submit" class="action action--primary action--regular">
                            <x-lucide-phone-call class="icon icon--sm" />
                            <span>{{ __('ui.join_prototype_session_a586902856') }}</span>
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('messages.actions') }}">
                        @csrf
                        <input type="hidden" name="action" value="end-message-call">
                        <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                        <input type="hidden" name="return_filter" value="{{ $activeFilter }}">
                        <button type="submit" class="action action--danger action--regular">
                            <x-lucide-phone-off class="icon icon--sm" />
                            <span>{{ __('ui.end_call_2fe13d93a1') }}</span>
                        </button>
                    </form>
                @endif
            </footer>
        @endif
    </div>
</section>
