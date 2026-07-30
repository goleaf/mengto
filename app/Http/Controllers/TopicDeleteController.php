<?php

namespace App\Http\Controllers;

use App\Actions\DeleteTopic;
use App\Models\ForumTopic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class TopicDeleteController extends Controller
{
    public function __invoke(ForumTopic $forumTopic, DeleteTopic $deleteTopic): RedirectResponse
    {
        Gate::authorize('delete', $forumTopic);
        $deleteTopic->handle($forumTopic);

        return to_route('pet-social.forum.index')->with('pawcircle.feedback', 'Topic deleted.');
    }
}
