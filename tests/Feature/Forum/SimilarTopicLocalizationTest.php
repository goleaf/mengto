<?php

declare(strict_types=1);

use AppModels\ForumAnswer;
use AppModels\ForumTopic;

test('similar topic suggestions return a complete localized metadata sentence', function () {
    app()->setLocale('ru');
    $topic = ForumTopic::factory()->create([
        'title' => 'Спокойная поездка с собакой в лифте',
        'category' => 'behavior',
    ]);
    ForumAnswer::factory()->create(['topic_id' => $topic->id]);

    $this->getJson(route('forum.topics.similar', [
        'q' => 'собакой лифте',
        'category' => 'behavior',
    ]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath(
            'data.0.meta',
            __('presentation.status_answers', [
                'status' => $topic->status->label(),
                'answers' => 1,
            ]),
        );
});
