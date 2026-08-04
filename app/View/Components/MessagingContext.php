<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class MessagingContext extends Component
{
    /** @var array<int, array{action: string, icon: string, label: string}> */
    public array $controls;

    /** @var array<int, array{action: string, icon: string, label: string}> */
    public array $safetyActions;

    /**
     * @param  array<string, mixed>  $conversation
     * @param  array<string, mixed>  $context
     * @param  array<int, array<string, mixed>>  $members
     * @param  array<string, mixed>|null  $poll
     * @param  array<int, array<string, mixed>>  $tasks
     * @param  array<string, mixed>|null  $professional
     * @param  array<int, array<string, string>>  $coverage
     */
    public function __construct(
        public array $conversation,
        public array $context,
        public array $members,
        public ?array $poll,
        public array $tasks,
        public ?array $professional,
        public string $activeFilter,
        public string $messageQuery,
        public array $coverage,
        public bool $detailsOpen = false,
    ) {
        $this->controls = [
            [
                'action' => 'pin-conversation',
                'icon' => 'pin',
                'label' => $conversation['pinned']
                    ? __('messaging.context.controls.unpin')
                    : __('messaging.context.controls.pin'),
            ],
            [
                'action' => 'mute-conversation',
                'icon' => $conversation['muted'] ? 'bell' : 'bell-off',
                'label' => $conversation['muted']
                    ? __('messaging.context.controls.unmute')
                    : __('messaging.context.controls.mute'),
            ],
            [
                'action' => 'archive-conversation',
                'icon' => 'archive',
                'label' => $conversation['archived']
                    ? __('messaging.context.controls.restore')
                    : __('messaging.context.controls.archive'),
            ],
            [
                'action' => 'mark-conversation-unread',
                'icon' => 'mail',
                'label' => __('messaging.context.controls.unread'),
            ],
        ];

        $this->safetyActions = [
            [
                'action' => 'restrict-conversation',
                'icon' => 'shield-minus',
                'label' => $conversation['restricted']
                    ? __('messaging.context.safety.unrestrict')
                    : __('messaging.context.safety.restrict'),
            ],
            [
                'action' => 'block-conversation',
                'icon' => 'ban',
                'label' => $conversation['blocked']
                    ? __('messaging.context.safety.unblock')
                    : __('messaging.context.safety.block'),
            ],
            [
                'action' => 'export-conversation',
                'icon' => 'download',
                'label' => __('messaging.context.safety.export'),
            ],
        ];
    }

    public function render(): View
    {
        return view('components.messaging-context');
    }
}
