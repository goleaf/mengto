@props(['conversation', 'call', 'boundary', 'activeFilter'])

<section
    class="messaging-call-stage"
    data-call-stage
    @if (! $call) hidden @endif
    aria-labelledby="call-stage-title"
>
    <div class="messaging-call-stage__backdrop" aria-hidden="true"></div>
    <div class="messaging-call-stage__dialog" role="dialog" aria-modal="true">
        @if ($call)
            <header>
                <div>
                    <p>{{ str($call['type'])->headline() }} call · {{ str($call['status'])->headline() }}</p>
                    <h2 id="call-stage-title">{{ $conversation['name'] }}</h2>
                    <span>{{ $conversation['pet'] }} · {{ $call['quality'] }}</span>
                </div>
                <form method="POST" action="{{ route('messages.actions') }}" data-call-end>
                    @csrf
                    <input type="hidden" name="action" value="end-message-call">
                    <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                    <input type="hidden" name="return_filter" value="{{ $activeFilter }}">
                    <button type="submit" class="messaging-icon-button" title="Close call">
                        <x-lucide-x class="icon" aria-hidden="true" />
                        <span class="sr-only">Close call</span>
                    </button>
                </form>
            </header>

            <div class="messaging-call-stage__preview">
                <video data-call-preview muted playsinline hidden></video>
                <div data-call-placeholder>
                    <img src="{{ $conversation['avatar'] }}" alt="" width="112" height="112">
                    <strong>{{ $conversation['name'] }}</strong>
                    <span data-call-device-status>Camera and microphone have not been requested.</span>
                </div>
                <span class="messaging-call-stage__recording">
                    <x-lucide-circle-dot class="icon icon--sm" aria-hidden="true" />
                    Recording off
                </span>
            </div>

            <div class="messaging-call-stage__checks">
                <button type="button" data-call-device="microphone"><x-lucide-mic class="icon icon--sm" /> Test microphone</button>
                @if ($call['type'] === 'video')
                    <button type="button" data-call-device="camera"><x-lucide-camera class="icon icon--sm" /> Preview camera</button>
                @endif
                <span><x-lucide-wifi class="icon icon--sm" /> Browser connection check</span>
            </div>

            <div class="messaging-call-stage__notice">
                <x-lucide-shield-check class="icon" aria-hidden="true" />
                <p><strong>Consent before connection</strong><span>{{ $boundary['transport'] }} {{ $boundary['recording'] }}</span></p>
            </div>

            <div class="messaging-call-stage__controls">
                @foreach ([
                    ['control' => 'microphone', 'icon' => $call['microphone'] ? 'mic' : 'mic-off', 'label' => $call['microphone'] ? 'Mute' : 'Unmute'],
                    ['control' => 'camera', 'icon' => $call['camera'] ? 'video' : 'video-off', 'label' => $call['camera'] ? 'Camera off' : 'Camera on'],
                    ['control' => 'captions', 'icon' => 'captions', 'label' => $call['captions'] ? 'Captions off' : 'Captions'],
                    ['control' => 'audio-only', 'icon' => 'phone', 'label' => 'Audio only'],
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
                            <span>Join prototype session</span>
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
                            <span>End call</span>
                        </button>
                    </form>
                @endif
            </footer>
        @endif
    </div>
</section>
