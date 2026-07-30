<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FeedAction extends Component
{
    public string $resolvedLabel;

    /** @param array<string, mixed> $payload */
    public function __construct(
        public string $label,
        public string $icon,
        public ?string $endpoint = null,
        public array $payload = [],
        public ?string $href = null,
        public bool $active = false,
        public ?string $activeLabel = null,
        public ?string $compactLabel = null,
    ) {
        $this->resolvedLabel = $active && $activeLabel ? $activeLabel : $label;
    }

    public function render(): View
    {
        return view('components.feed-action');
    }
}
