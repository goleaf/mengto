<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseRequest;
use App\Services\DirectoryFilter;
use App\Services\PreviewService;
use Illuminate\Contracts\View\View;

class PetDirectoryPreviewController extends Controller
{
    public function __invoke(
        BrowseRequest $request,
        PreviewService $preview,
        DirectoryFilter $filter,
    ): View {
        $data = $preview->petDirectoryData();
        $parameters = $request->validated();
        $data['directoryPets'] = $filter->apply(
            $data['directoryPets'],
            $parameters['q'] ?? null,
            $parameters['filter'] ?? null,
            $parameters['sort'] ?? null,
            [
                'dogs' => ['dog'],
                'cats' => ['cat'],
                'small-pets' => ['rabbit', 'bird', 'small'],
            ],
            ['name', 'species', 'breed', 'owner', 'neighborhood', 'status', 'traits'],
        );

        return view('pets.index', [
            ...$data,
            'directoryQuery' => $parameters['q'] ?? '',
            'activeFilter' => $parameters['filter'] ?? 'all-pets',
            'activeSort' => $parameters['sort'] ?? 'recommended',
        ]);
    }
}
