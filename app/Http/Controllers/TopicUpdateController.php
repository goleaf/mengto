<?php

namespace App\Http\Controllers;

use App\Actions\UpdateTopic;
use App\Http\Requests\UpdateTopicRequest;
use App\Models\ForumTopic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class TopicUpdateController extends Controller
{
    public function __invoke(
        UpdateTopicRequest $request,
        ForumTopic $forumTopic,
        UpdateTopic $updateTopic,
    ): RedirectResponse {
        Gate::authorize('update', $forumTopic);
        $topic = $updateTopic->handle($forumTopic, $request->validated());

        return to_route('forum.topics.show', $topic)
            ->with('feedback', 'Topic updated.');
    }
}
