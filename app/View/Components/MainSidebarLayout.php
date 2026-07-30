<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MainSidebarLayout extends Component
{
    public string $layoutClass;

    public ?string $sidebarClass;

    public function __construct(public string $variant = 'default')
    {
        $this->layoutClass = match ($variant) {
            'compact' => 'gap-4 md:grid-cols-[minmax(0,1fr)_18rem] lg:grid-cols-[minmax(0,1fr)_20rem]',
            default => 'gap-5 lg:grid-cols-[minmax(0,1fr)_20rem]',
        };
        $this->sidebarClass = $variant === 'default'
            ? 'md:grid-cols-3 lg:grid-cols-1'
            : null;
    }

    public function render(): View
    {
        return view('components.main-sidebar-layout');
    }
}
