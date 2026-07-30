<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseKnowledgeRequest;
use App\Services\KnowledgePresenter;
use Illuminate\Contracts\View\View;

class KnowledgeController extends Controller
{
    public function __invoke(BrowseKnowledgeRequest $request, KnowledgePresenter $presenter): View
    {
        return view('knowledge.index', $presenter->library($request->validated()));
    }
}
