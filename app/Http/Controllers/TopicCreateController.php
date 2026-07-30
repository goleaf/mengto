<?php

namespace App\Http\Controllers;

use App\Models\ForumTopic;
use App\Services\ForumPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class TopicCreateController extends Controller
{
    public function __invoke(ForumPresenter $presenter): View
    {
        Gate::authorize('create', ForumTopic::class);

        return view('pet-social.forum.editor', $presenter->editor());
    }
}
