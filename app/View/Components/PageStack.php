<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PageStack extends Component
{
    public string $gapClass;

    public function __construct(public string $gap = 'page')
    {
        $this->gapClass = match ($gap) {
            'section' => 'gap-4',
            'compact' => 'gap-3',
            default => 'gap-5',
        };
    }

    public function render(): View
    {
        return view('components.page-stack');
    }
}
