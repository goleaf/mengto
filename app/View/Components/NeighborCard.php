<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class NeighborCard extends Component
{
    public string $neighborKey;

    public bool $followed;

    /**
     * @param  array<string, mixed>  $neighbor
     */
    public function __construct(
        public array $neighbor,
        public bool $eager = false,
    ) {
        $this->neighborKey = $neighbor['key'] ?? Str::slug($neighbor['name']);
        $this->followed = $neighbor['followed'] ?? false;
    }

    public function render(): View
    {
        return view('components.neighbor-card');
    }
}
