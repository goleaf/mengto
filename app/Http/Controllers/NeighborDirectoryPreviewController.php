<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseRequest;
use App\Services\DirectoryFilter;
use App\Services\PreviewService;
use Illuminate\Contracts\View\View;

class NeighborDirectoryPreviewController extends Controller
{
    public function __invoke(
        BrowseRequest $request,
        PreviewService $preview,
        DirectoryFilter $filter,
    ): View {
        $data = $preview->neighborDirectoryData();
        $parameters = $request->validated();
        $data['directoryNeighbors'] = $filter->apply(
            $data['directoryNeighbors'],
            $parameters['q'] ?? null,
            $parameters['filter'] ?? null,
            $parameters['sort'] ?? 'closest',
            [
                'dog-people' => ['dog-people'],
                'cat-people' => ['cat-people'],
                'foster-network' => ['foster-network'],
            ],
            ['name', 'category', 'neighborhood', 'pet', 'status', 'interests', 'search_tokens'],
        );

        return view('neighbors.index', [
            ...$data,
            'directoryQuery' => $parameters['q'] ?? '',
            'activeFilter' => $parameters['filter'] ?? 'recommended',
            'activeSort' => $parameters['sort'] ?? 'closest',
        ]);
    }
}
