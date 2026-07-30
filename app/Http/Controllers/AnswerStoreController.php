<?php

namespace App\Http\Controllers;

use App\Actions\CreateAnswer;
use App\Http\Requests\StoreAnswerRequest;
use App\Models\ForumAnswer;
use App\Models\ForumTopic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class AnswerStoreController extends Controller
{
    public function __invoke(
        StoreAnswerRequest $request,
        ForumTopic $forumTopic,
        CreateAnswer $createAnswer,
    ): RedirectResponse {
        Gate::authorize('create', ForumAnswer::class);
        $createAnswer->handle($forumTopic, $request->validated());

        return to_route('forum.topics.show', $forumTopic)
            ->with('feedback', 'Answer published.');
    }
}
