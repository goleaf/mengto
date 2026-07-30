<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowsePawCircleRequest;
use App\Services\PawCircleDirectoryFilter;
use App\Services\PawCirclePreviewService;
use Illuminate\Contracts\View\View;

class NeighborDirectoryPreviewController extends Controller
{
    public function __invoke(
        BrowsePawCircleRequest $request,
        PawCirclePreviewService $preview,
        PawCircleDirectoryFilter $filter,
    ): View {
        $data = $preview->neighborDirectoryData();
        $parameters = $request->validated();
        $data['directoryNeighbors'] = $filter->apply(
            $data['directoryNeighbors'],
            $parameters['q'] ?? null,
            $parameters['filter'] ?? null,
            $parameters['sort'] ?? 'closest',
            [
                'dog-people' => ['dog', 'walk', 'training'],
                'cat-people' => ['cat', 'foster'],
                'foster-network' => ['foster', 'care', 'senior'],
            ],
            ['name', 'category', 'neighborhood', 'pet', 'status', 'interests'],
        );

        return view('pet-social.neighbors.index', [
            ...$data,
            'directoryQuery' => $parameters['q'] ?? '',
            'activeFilter' => $parameters['filter'] ?? 'recommended',
            'activeSort' => $parameters['sort'] ?? 'closest',
        ]);
    }
}
