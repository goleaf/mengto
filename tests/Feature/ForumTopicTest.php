<?php

use App\Enums\ForumTopicStatus;
use App\Enums\KnowledgeCollaboratorRole;
use App\Enums\KnowledgeStatus;
use App\Models\ForumAnswer;
use App\Models\ForumComment;
use App\Models\ForumReport;
use App\Models\ForumTopic;
use App\Models\ForumVote;
use App\Models\KnowledgeArticle;
use App\Models\User;
use Database\Seeders\ForumModerationDefinitionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('owner can publish a structured topic with safe media storage', function () {
    Storage::fake('public');

    $response = $this->post(route('forum.topics.store'), [
        'type' => 'question',
        'category' => 'behavior',
        'subcategory' => 'fear',
        'pet_key' => 'scout',
        'title' => 'How can I help Birch feel calm near the lift doors?',
        'body' => 'Birch became worried after a metal cart fell near the lift. He can currently observe the doors from a distance and recover when we move away.',
        'tried' => 'We stopped before he froze and kept sessions short.',
        'desired_answer' => 'professional-opinion',
        'tags' => 'fear, lift, confidence, fear',
        'location' => 'Vilnius',
        'visibility' => 'public',
        'comment_policy' => 'registered',
        'language' => 'en',
        'intent' => 'publish',
        'photo_alt' => 'Birch waiting at a comfortable distance from the lift',
        'photos' => [UploadedFile::fake()->image('scout.jpg', 1200, 800)],
    ]);

    $topic = ForumTopic::query()->firstOrFail();

    $response->assertRedirect(route('forum.topics.show', $topic));
    expect($topic)
        ->author_key->toBe('test-member')
        ->pet_name->toBe('Birch')
        ->status->toBe(ForumTopicStatus::Published)
        ->and($topic->tags)->toBe(['fear', 'lift', 'confidence'])
        ->and($topic->media)->toHaveCount(1);

    Storage::disk('public')->assertExists($topic->media[0]['path']);
    expect($topic->media[0]['path'])->toEndWith('.webp');
});

test('topic creation validates structure before persistence', function () {
    $this->from(route('forum.topics.create'))
        ->post(route('forum.topics.store'), [
            'type' => 'question',
            'category' => 'behavior',
            'title' => 'Help',
            'body' => 'Too short.',
            'visibility' => 'public',
            'comment_policy' => 'registered',
            'language' => 'en',
            'intent' => 'publish',
        ])
        ->assertRedirect(route('forum.topics.create'))
        ->assertSessionHasErrors(['title', 'body']);

    expect(ForumTopic::query()->count())->toBe(0);
});

test('answers votes acceptance and knowledge conversion remain idempotent', function () {
    $topic = ForumTopic::factory()->create([
        'author_id' => $this->authenticatedUser->id,
        'author_key' => 'test-member',
        'title' => 'How can I prepare a calm carrier routine for my cat?',
        'category' => 'behavior',
        'tags' => ['cat', 'carrier'],
    ]);
    $answerAuthor = User::factory()->create();

    $this->actingAs($answerAuthor);
    $this->post(route('forum.answers.store', $topic), [
        'body' => 'Leave the carrier open in normal living space and reward voluntary approaches before touching the door.',
        'experience_type' => 'personal-experience',
        'sources' => "https://catvets.com/resource/feline-behavior-guidelines/\n",
    ])->assertRedirect(route('forum.topics.show', $topic));

    $answer = ForumAnswer::query()->firstOrFail();
    $this->actingAs($this->authenticatedUser);

    $votePayload = [
        'action' => 'vote-answer',
        'answer_id' => $answer->id,
        'value' => 'helpful',
    ];
    $this->post(route('forum.actions'), $votePayload)
        ->assertRedirect()
        ->assertSessionHasNoErrors();
    $this->post(route('forum.actions'), $votePayload)
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(ForumVote::query()->count())->toBe(1)
        ->and($answer->refresh()->helpful_count)->toBe(1);

    $this->post(route('forum.actions'), [
        'action' => 'accept-answer',
        'answer_id' => $answer->id,
    ])->assertRedirect();

    expect($topic->refresh())
        ->status->toBe(ForumTopicStatus::Solved)
        ->accepted_answer_id->toBe($answer->id);

    $this->post(route('forum.actions'), [
        'action' => 'convert-to-knowledge',
        'topic_id' => $topic->id,
    ])->assertRedirect();
    $this->post(route('forum.actions'), [
        'action' => 'convert-to-knowledge',
        'topic_id' => $topic->id,
    ])->assertRedirect();

    $article = KnowledgeArticle::query()->firstOrFail();

    expect(KnowledgeArticle::query()->count())->toBe(1)
        ->and($article->versions()->count())->toBe(1)
        ->and($article->source_topic_id)->toBe($topic->id)
        ->and($article->discussion_topic_id)->toBe($topic->id)
        ->and($article->translation_group_key)->toBe("topic-{$topic->id}")
        ->and($article->status)->toBe(KnowledgeStatus::SubmittedForReview)
        ->and($article->activeCollaborators()->count())->toBe(2)
        ->and($article->activeCollaborators()
            ->orderBy('role')
            ->pluck('role')
            ->all())->toBe([
                KnowledgeCollaboratorRole::Contributor,
                KnowledgeCollaboratorRole::Maintainer,
            ])
        ->and($article->workflowEvents()->count())->toBe(1);
});

test('comments cannot cross topic boundaries or exceed one reply level', function () {
    $topic = ForumTopic::factory()->create();
    $otherTopic = ForumTopic::factory()->create();
    $answer = ForumAnswer::factory()->create(['topic_id' => $otherTopic->id]);

    $this->from(route('forum.topics.show', $topic))
        ->post(route('forum.comments.store', $topic), [
            'answer_id' => $answer->id,
            'body' => 'This comment should not attach across topics.',
        ])
        ->assertRedirect(route('forum.topics.show', $topic))
        ->assertSessionHasErrors('answer_id');

    expect(ForumComment::query()->count())->toBe(0);
});

test('medical topics show an emergency boundary and reports enter moderation', function () {
    $this->seed(ForumModerationDefinitionSeeder::class);
    $topic = ForumTopic::factory()->medical()->create([
        'is_urgent' => true,
        'title' => 'My dog is breathing heavily and cannot stand normally',
    ]);

    $this->get(route('forum.topics.show', $topic))
        ->assertOk()
        ->assertSee('The forum is not emergency veterinary care')
        ->assertSee('call a clinic now');

    $this->post(route('forum.actions'), [
        'action' => 'report-topic',
        'topic_id' => $topic->id,
        'reason' => 'dangerous-advice',
        'details' => 'The thread includes a risky treatment suggestion.',
        'truthfulness_confirmed' => '1',
        'immediate_safety' => '1',
    ])->assertRedirect(route('forum.topics.show', $topic));

    expect(ForumReport::query()->firstOrFail())
        ->priority->toBe('critical')
        ->status->toBe('received');
});
