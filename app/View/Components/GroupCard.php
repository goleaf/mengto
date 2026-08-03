<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class GroupCard extends Component
{
    public string $groupKey;

    public bool $joined;

    /**
     * @var array<string, mixed>
     */
    public array $primary;

    /**
     * @var array<int, array{label: string, value: string}>
     */
    public array $metrics;

    /**
     * @param  array<string, mixed>  $group
     */
    public function __construct(
        public array $group,
        public bool $eager = false,
    ) {
        $this->groupKey = $group['key'] ?? Str::slug($group['name']);
        $this->joined = $group['joined'] ?? false;
        $this->primary = $group['primary_action'] ?? [
            'label' => $this->joined
                ? __('groups.directory.actions.joined')
                : __('groups.directory.actions.join_group'),
            'icon' => $this->joined ? 'check' : 'user-plus',
            'variant' => 'paper',
            'endpoint' => route('actions.perform'),
            'payload' => [
                'action' => 'toggle-group',
                'target' => $this->groupKey,
                'label' => $group['name'],
            ],
            'active' => $this->joined,
        ];
        $this->metrics = [
            [
                'label' => __('groups.directory.card.members'),
                'value' => $group['members'],
            ],
            [
                'label' => __('groups.directory.card.activity'),
                'value' => $group['activity'],
            ],
        ];
    }

    public function render(): View
    {
        return view('components.group-card');
    }
}
