<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ActionControl extends Component
{
    public string $resolvedLabel;

    public ?string $resolvedIcon;

    public string $resolvedLoadingLabel;

    /** @var array<int|string, string|bool> */
    public array $classes;

    public bool $isDisabled;

    /** @param array<string, mixed> $payload */
    public function __construct(
        public string $label,
        public ?string $icon = null,
        public string $variant = 'surface',
        public string $size = 'compact',
        public ?string $endpoint = null,
        public array $payload = [],
        public ?string $href = null,
        public string $type = 'button',
        public bool $active = false,
        public ?string $activeLabel = null,
        public ?string $activeIcon = null,
        public ?string $loadingLabel = null,
        public ?bool $pressed = null,
        public bool $disabled = false,
        public ?string $name = null,
        public mixed $value = null,
    ) {
        $this->resolvedLabel = $active && $activeLabel ? $activeLabel : $label;
        $this->resolvedIcon = $active && $activeIcon ? $activeIcon : $icon;
        $this->resolvedLoadingLabel = $loadingLabel ?: __('presentation.action_processing');
        $this->classes = [
            'action',
            'action--'.$variant,
            'action--'.$size,
            'action--active' => $active,
        ];
        $this->isDisabled = $disabled
            || ($endpoint === null && $href === null && $type === 'button');
    }

    public function render(): View
    {
        return view('components.action-control');
    }
}
