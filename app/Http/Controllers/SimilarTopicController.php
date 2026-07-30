<?php

namespace App\Http\Controllers;

use App\Http\Requests\FindSimilarTopicsRequest;
use App\Http\Resources\SimilarTopicResource;
use App\Services\ForumPresenter;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SimilarTopicController extends Controller
{
    public function __invoke(
        FindSimilarTopicsRequest $request,
        ForumPresenter $presenter,
    ): AnonymousResourceCollection {
        $data = $request->validated();

        return SimilarTopicResource::collection(
            $presenter->similar((string) $data['q'], $data['category'] ?? null),
        );
    }
}
