<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TextLink extends Component
{
    public ?string $resolvedHref;

    /** @param array<string, mixed> $routeParameters */
    public function __construct(
        public ?string $href = null,
        public ?string $routeName = null,
        public array $routeParameters = [],
        public ?string $icon = null,
        public string $variant = 'inline',
    ) {
        $this->resolvedHref = $href
            ?? ($routeName ? route($routeName, $routeParameters) : null);
    }

    public function render(): View
    {
        return view('components.text-link');
    }
}
