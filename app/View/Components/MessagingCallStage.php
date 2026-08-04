<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class MessagingCallStage extends Component
{
    /** @var array<int, array{control: string, icon: string, label: string}> */
    public array $controls = [];

    /**
     * @param  array<string, mixed>  $conversation
     * @param  array<string, mixed>|null  $call
     * @param  array{transport: string, recording: string, emergency: string}  $boundary
     */
    public function __construct(
        public array $conversation,
        public ?array $call,
        public array $boundary,
        public string $activeFilter,
    ) {
        if ($call === null) {
            return;
        }

        $this->controls[] = [
            'control' => 'microphone',
            'icon' => $call['microphone'] ? 'mic' : 'mic-off',
            'label' => $call['microphone']
                ? __('messaging.call_stage.controls.mute')
                : __('messaging.call_stage.controls.unmute'),
        ];

        if ($call['type_code'] === 'video') {
            $this->controls[] = [
                'control' => 'camera',
                'icon' => $call['camera'] ? 'video' : 'video-off',
                'label' => $call['camera']
                    ? __('messaging.call_stage.controls.camera_off')
                    : __('messaging.call_stage.controls.camera_on'),
            ];
        }

        $this->controls[] = [
            'control' => 'captions',
            'icon' => 'captions',
            'label' => $call['captions']
                ? __('messaging.call_stage.controls.captions_off')
                : __('messaging.call_stage.controls.captions_on'),
        ];

        if ($call['type_code'] === 'video') {
            $this->controls[] = [
                'control' => 'audio-only',
                'icon' => 'phone',
                'label' => __('messaging.call_stage.controls.audio_only'),
            ];
        }

        if ($call['status_code'] === 'connected') {
            $this->controls[] = [
                'control' => 'reconnect',
                'icon' => 'refresh-cw',
                'label' => __('messaging.call_stage.controls.reconnect'),
            ];
        }
    }

    public function render(): View
    {
        return view('components.messaging-call-stage');
    }
}
