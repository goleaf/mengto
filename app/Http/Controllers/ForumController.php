<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseForumRequest;
use App\Services\ForumPresenter;
use Illuminate\Contracts\View\View;

class ForumController extends Controller
{
    public function __invoke(BrowseForumRequest $request, ForumPresenter $presenter): View
    {
        return view('pet-social.forum.index', $presenter->directory($request->validated()));
    }
}
