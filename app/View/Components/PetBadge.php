<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PetBadge extends Component
{
    public string $icon;

    public function __construct(
        public string $type,
        public string $tone = 'surface',
        public string $size = 'compact',
    ) {
        $this->icon = match (strtolower($type)) {
            'dog' => 'dog',
            'cat' => 'cat',
            'bird' => 'bird',
            'rabbit' => 'rabbit',
            default => 'paw-print',
        };
    }

    public function render(): View
    {
        return view('components.pet-badge');
    }
}
