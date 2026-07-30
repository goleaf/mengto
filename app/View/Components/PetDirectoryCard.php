<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class PetDirectoryCard extends Component
{
    public string $petKey;

    public bool $followed;

    /**
     * @param  array<string, mixed>  $pet
     */
    public function __construct(
        public array $pet,
        public bool $eager = false,
    ) {
        $this->petKey = $pet['key'] ?? Str::slug($pet['name']);
        $this->followed = $pet['followed'] ?? false;
    }

    public function render(): View
    {
        return view('components.pet-directory-card');
    }
}
