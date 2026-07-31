<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\View\Component;

class PlaceDirectory extends Component
{
    /** @var array<string, mixed> */
    public array $filters;

    /** @var array<string, mixed> */
    public array $queryParameters;

    /** @var array<int, array{label: string, url: string, current: bool}> */
    public array $modeLinks;

    /** @var array<int, array{label: string, url: string, current: bool, icon: string}> */
    public array $viewLinks;

    /** @var array<int, array{label: string, url: string, current: bool}> */
    public array $layerLinks;

    /** @var array<string, mixed> */
    public array $sortParameters;

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
        $this->modeLinks = $this->navigationLinks(
            $places['mode_options'],
            'mode',
            $this->filters['mode'],
        );
        $this->viewLinks = $this->viewLinks($places['view_options']);
        $this->layerLinks = $this->navigationLinks(
            $places['layer_options'],
            'layer',
            $this->filters['layer'],
        );
        $this->sortParameters = Arr::where(
            Arr::except($this->queryParameters, ['sort', 'selected']),
            static fn (mixed $value): bool => $value !== null && $value !== '',
        );
    }

    public function render(): View
    {
        return view('components.place-directory');
    }

    /**
     * @param  array<string, string>  $options
     * @return array<int, array{label: string, url: string, current: bool}>
     */
    private function navigationLinks(array $options, string $parameter, string $currentValue): array
    {
        return collect($options)
            ->map(fn (string $label, string $value): array => [
                'label' => $label,
                'url' => $this->browseUrl([$parameter => $value]),
                'current' => $currentValue === $value,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, string>  $options
     * @return array<int, array{label: string, url: string, current: bool, icon: string}>
     */
    private function viewLinks(array $options): array
    {
        return collect($options)
            ->map(fn (string $label, string $value): array => [
                'label' => $label,
                'url' => $this->browseUrl(['view' => $value]),
                'current' => $this->filters['view'] === $value,
                'icon' => match ($value) {
                    'map' => 'map',
                    'list' => 'list',
                    'fullscreen' => 'maximize-2',
                    'route' => 'route',
                    default => 'panel-left',
                },
            ])
            ->values()
            ->all();
    }

    /** @param array<string, mixed> $overrides */
    private function browseUrl(array $overrides): string
    {
        $parameters = Arr::where(
            [...$this->queryParameters, ...$overrides],
            static fn (mixed $value): bool => $value !== null && $value !== '',
        );

        return route('places.index', $parameters);
    }
}
