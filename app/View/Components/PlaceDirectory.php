<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PlaceDirectory extends Component
{
    /** @var array<string, mixed> */
    public array $filters;

    /** @var array<string, mixed> */
    public array $queryParameters;

    /** @param array<string, mixed> $places */
    public function __construct(public array $places)
    {
        $this->filters = $places['filters'];
        $this->queryParameters = [
            'q' => $places['query'],
            'area' => $this->filters['area'],
            'category' => $this->filters['category'],
            'species' => $this->filters['species'],
            'size' => $this->filters['size'],
            'distance' => $this->filters['distance'],
            'open_now' => $this->filters['open_now'] ? 1 : null,
            'leash' => $this->filters['leash'],
            'accessibility' => $this->filters['accessibility'],
            'safety' => $this->filters['safety'],
            'price' => $this->filters['price'],
            'rating' => $this->filters['rating'],
            'verification' => $this->filters['verification'],
            'crowd' => $this->filters['crowd'],
            'visit_time' => $this->filters['visit_time'],
            'pet' => $this->filters['pet'],
            'sort' => $this->filters['sort'],
            'view' => $this->filters['view'],
            'mode' => $this->filters['mode'],
            'layer' => $this->filters['layer'],
            'selected' => $places['selected']['key'] ?? null,
            'emergency' => $places['emergency'] ? 1 : null,
        ];
    }

    public function render(): View
    {
        return view('components.place-directory');
    }
}
