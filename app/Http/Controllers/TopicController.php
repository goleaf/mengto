<?php

namespace App\Http\Controllers;

use App\Models\ForumTopic;
use App\Services\ForumPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class TopicController extends Controller
{
    public function __invoke(ForumTopic $forumTopic, ForumPresenter $presenter): View
    {
        Gate::authorize('view', $forumTopic);

        return view('pet-social.forum.show', $presenter->topic($forumTopic));
    }
}
