<?php

namespace App\Http\Controllers;

use App\Models\ForumTopic;
use App\Services\ForumPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class TopicEditController extends Controller
{
    public function __invoke(ForumTopic $forumTopic, ForumPresenter $presenter): View
    {
        Gate::authorize('update', $forumTopic);

        return view('pet-social.forum.editor', $presenter->editor($forumTopic));
    }
}
