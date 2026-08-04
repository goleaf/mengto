<?php

declare(strict_types=1);

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

    public string $statusLabel;

    public string $statusCode;

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
            'announcement' => 'megaphone',
            'status' => 'circle-dot',
            'warning' => 'triangle-alert',
            'professional' => 'badge-check',
        ];

        $type = (string) $message['type'];
        $this->icon = $icons[$type] ?? 'message-circle';
        $this->typeLabel = $this->resolveTypeLabel($type);
        $this->structured = ! in_array($type, ['text', 'deleted'], true);
        $this->replyText = $message['sender'].': '.Str::limit($message['body'], 100);
        $this->reactionLabel = $this->resolveReactionLabel($message['reaction'] ?? null);
        $this->statusCode = (string) ($message['status_code'] ?? ($message['mine'] ? 'read' : 'delivered'));
        $this->statusLabel = match ($this->statusCode) {
            'sent' => __('messaging.message.status.sent'),
            'read' => __('messaging.message.status.read'),
            default => __('messaging.message.status.delivered'),
        };
        $this->editable = $message['mine']
            && Str::startsWith($message['id'], 'local-');
    }

    private function resolveTypeLabel(string $type): string
    {
        return match ($type) {
            'audio' => __('messaging.message.types.audio'),
            'image' => __('messaging.message.types.image'),
            'video' => __('messaging.message.types.video'),
            'file' => __('messaging.message.types.file'),
            'place' => __('messaging.message.types.place'),
            'event' => __('messaging.message.types.event'),
            'expert' => __('messaging.message.types.expert'),
            'listing' => __('messaging.message.types.listing'),
            'pet' => __('messaging.message.types.pet'),
            'poll' => __('messaging.message.types.poll'),
            'task' => __('messaging.message.types.task'),
            'walk' => __('messaging.message.types.walk'),
            'call' => __('messaging.message.types.call'),
            'system' => __('messaging.message.types.system'),
            'deleted' => __('messaging.message.types.deleted'),
            'announcement' => __('messaging.message.types.announcement'),
            'status' => __('messaging.message.types.status'),
            'warning' => __('messaging.message.types.warning'),
            'professional' => __('messaging.message.types.professional'),
            default => __('messaging.message.types.text'),
        };
    }

    private function resolveReactionLabel(mixed $reaction): ?string
    {
        return match ($reaction) {
            'like' => __('messaging.message.reactions.like'),
            'support' => __('messaging.message.reactions.support'),
            'thanks' => __('messaging.message.reactions.thanks'),
            'funny' => __('messaging.message.reactions.funny'),
            'understood' => __('messaging.message.reactions.understood'),
            'care' => __('messaging.message.reactions.care'),
            default => null,
        };
    }

    public function render(): View
    {
        return view('components.messaging-message');
    }
}
