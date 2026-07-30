<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ResponsiveImage extends Component
{
    /** @var array<int, string> */
    public array $sourceCandidates;

    public bool $hasResponsiveSources;

    public string $fallbackSource;

    public function __construct(
        public string $src,
        public string $alt,
        public int $width,
        public int $height,
        public ?string $small = null,
        public ?string $medium = null,
        public int $smallWidth = 576,
        public int $mediumWidth = 900,
        public string $sizes = '100vw',
        public bool $eager = false,
    ) {
        $this->sourceCandidates = array_values(array_filter([
            $small ? "{$small} {$smallWidth}w" : null,
            $medium ? "{$medium} {$mediumWidth}w" : null,
        ]));
        $this->hasResponsiveSources = $this->sourceCandidates !== [];

        if ($this->hasResponsiveSources) {
            $this->sourceCandidates[] = "{$src} {$width}w";
        }

        $this->fallbackSource = $small ?? $medium ?? $src;
    }

    public function render(): View
    {
        return view('components.responsive-image');
    }
}
