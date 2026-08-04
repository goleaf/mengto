<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class MessagingComposer extends Component
{
    /** @var array<int, array{type: string, icon: string, label: string}> */
    public array $tools;

    /**
     * @param  array<string, mixed>  $conversation
     */
    public function __construct(
        public array $conversation,
        public string $activeFilter,
        public string $sender,
    ) {
        $this->tools = [
            ['type' => 'image', 'icon' => 'image', 'label' => __('messaging.composer.tools.image')],
            ['type' => 'video', 'icon' => 'video', 'label' => __('messaging.composer.tools.video')],
            ['type' => 'file', 'icon' => 'paperclip', 'label' => __('messaging.composer.tools.file')],
            ['type' => 'audio', 'icon' => 'mic', 'label' => __('messaging.composer.tools.audio')],
            ['type' => 'pet', 'icon' => 'paw-print', 'label' => __('messaging.composer.tools.pet')],
            ['type' => 'place', 'icon' => 'map-pin', 'label' => __('messaging.composer.tools.place')],
            ['type' => 'event', 'icon' => 'calendar-days', 'label' => __('messaging.composer.tools.event')],
            ['type' => 'task', 'icon' => 'list-checks', 'label' => __('messaging.composer.tools.task')],
        ];
    }

    public function render(): View
    {
        return view('components.messaging-composer');
    }
}
