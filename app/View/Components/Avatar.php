<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Avatar extends Component
{
    public int $dimension;

    public function __construct(
        public string $src,
        public string $alt = '',
        public string $size = 'compact',
        public string $shape = 'circle',
        public bool $lazy = false,
        public bool $decorative = false,
    ) {
        $this->dimension = match ($size) {
            'header' => 40,
            'thread' => 44,
            'profile' => 64,
            default => 48,
        };
    }

    public function render(): View
    {
        return view('components.avatar');
    }
}
