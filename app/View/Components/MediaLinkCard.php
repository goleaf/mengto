<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MediaLinkCard extends Component
{
    public string $href;

    /** @param array<string, mixed> $item */
    public function __construct(
        public array $item,
        public bool $eager = false,
    ) {
        $this->href = $item['href']
            ?? route($item['route'], $item['route_parameters'] ?? []);
    }

    public function render(): View
    {
        return view('components.media-link-card');
    }
}
