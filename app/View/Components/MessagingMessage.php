<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class MessagingMessage extends Component
{
    public string $icon;

    public string $typeLabel;

    public bool $structured;

    public string $replyText;

    public ?string $reactionLabel;

    public bool $editable;

    /**
     * @param  array<string, mixed>  $message
     * @param  array<string, mixed>  $conversation
     */
    public function __construct(
        public array $message,
        public array $conversation,
    ) {
        $icons = [
            'audio' => 'audio-lines',
            'image' => 'image',
            'video' => 'video',
            'file' => 'file-text',
            'place' => 'map-pin',
            'event' => 'calendar-days',
            'expert' => 'stethoscope',
            'listing' => 'store',
            'pet' => 'paw-print',
            'poll' => 'list-checks',
            'task' => 'clipboard-check',
            'walk' => 'route',
            'call' => 'phone-call',
            'system' => 'sparkles',
            'deleted' => 'message-square-off',
            'text' => 'message-circle',
        ];

        $this->icon = $icons[$message['type']] ?? 'message-circle';
        $this->typeLabel = Str::headline($message['type']);
        $this->structured = ! in_array($message['type'], ['text', 'deleted'], true);
        $this->replyText = $message['sender'].': '.Str::limit($message['body'], 100);
        $this->reactionLabel = filled($message['reaction'] ?? null)
            ? Str::headline($message['reaction'])
            : null;
        $this->editable = $message['mine']
            && Str::startsWith($message['id'], 'local-');
    }

    public function render(): View
    {
        return view('components.messaging-message');
    }
}
