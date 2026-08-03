<?php

use App\Enums\ForumTopicStatus;
use App\Enums\ForumVisibility;
use App\Models\ForumAnswer;
use App\Models\ForumBlock;
use App\Models\ForumTopic;
use App\Models\KnowledgeArticle;

test('forum directory filters searchable public topics and keeps private topics out', function () {
    $visible = ForumTopic::factory()->create([
        'title' => 'A calm carrier routine for a nervous rescue cat',
        'category' => 'behavior',
        'tags' => ['cat', 'carrier'],
    ]);
    ForumAnswer::factory()->expert()->create([
        'topic_id' => $visible->id,
        'body' => 'Keep the carrier available as ordinary furniture and reward voluntary investigation.',
    ]);
    ForumTopic::factory()->create([
        'title' => 'Private family notes about our cat carrier',
        'category' => 'behavior',
        'visibility' => ForumVisibility::Private,
    ]);
    ForumTopic::factory()->create([
        'title' => 'A public travel checklist for a senior dog',
        'category' => 'travel',
    ]);
    KnowledgeArticle::factory()->create([
        'title' => 'Carrier confidence checklist',
        'category' => 'behavior',
    ]);

    $response = $this->get(route('forum.index', [
        'q' => 'carrier',
        'category' => 'behavior',
        'filter' => 'all',
        'sort' => 'active',
    ]));

    $response
        ->assertOk()
        ->assertSee('Ask well. Find what lasts.')
        ->assertSee($visible->title)
        ->assertSee('Carrier confidence checklist')
        ->assertDontSee('Private family notes')
        ->assertDontSee('public travel checklist');
});

test('forum directory keeps category navigation in the main content and filters by the selected child', function () {
    $selected = ForumTopic::factory()->create([
        'title' => 'Preventive care schedule for a newly adopted dog',
        'category' => 'health',
        'subcategory' => 'health/preventive-care',
    ]);
    $sibling = ForumTopic::factory()->create([
        'title' => 'Preparing for a routine veterinary visit',
        'category' => 'health',
        'subcategory' => 'health/veterinary-visits',
    ]);
    ForumTopic::factory()->create([
        'title' => 'Daily feeding portions for an adult cat',
        'category' => 'nutrition',
        'subcategory' => 'nutrition/daily-feeding',
    ]);

    $rootResponse = $this->get(route('forum.index', ['category' => 'health']))->assertOk();
    $rootXPath = responseXPath($rootResponse);

    expect($rootXPath->query('//aside//nav[@data-forum-category-tree]')->length)
        ->toBe(0)
        ->and($rootXPath->query('//*[@data-forum-directory-main]//*[@data-forum-category-navigator]')->length)
        ->toBe(1)
        ->and($rootXPath->query('//*[@data-forum-category-navigator]//*[@data-category-root]')->length)
        ->toBe(44)
        ->and($rootXPath->query('//*[@data-forum-category-navigator]//*[@data-subcategory-list="health"]')->length)
        ->toBe(1)
        ->and($rootXPath->query('//*[@data-forum-category-navigator]//*[@data-subcategory-list="nutrition"]')->length)
        ->toBe(0);
    $rootResponse
        ->assertSee($selected->title)
        ->assertSee($sibling->title);

    $childResponse = $this->get(route('forum.index', [
        'category' => 'health/preventive-care',
    ]))->assertOk();
    $childXPath = responseXPath($childResponse);

    expect($childXPath->query('//*[@data-forum-category-navigator]//a[@data-category-root="health" and @data-active-root="true"]')->length)
        ->toBe(1)
        ->and($childXPath->query('//*[@data-forum-category-navigator]//a[@data-category-child="health/preventive-care" and @aria-current="page"]')->length)
        ->toBe(1);
    $childResponse
        ->assertSee($selected->title)
        ->assertDontSee($sibling->title)
        ->assertDontSee('Daily feeding portions for an adult cat');
});

test('forum directory presents subcategory names with readable sentence capitalization', function () {
    $response = $this->get(route('forum.index', [
        'category' => 'training-education',
    ]))->assertOk();

    $response
        ->assertSee('Training foundations')
        ->assertSee('Positive reinforcement')
        ->assertDontSee('>training foundations<', escape: false)
        ->assertDontSee('>positive reinforcement<', escape: false);
});

test('blocked authors disappear from the directory without exposing the block', function () {
    ForumTopic::factory()->create([
        'author_key' => 'blocked-author',
        'title' => 'A noisy topic from a blocked author account',
    ]);
    ForumTopic::factory()->create([
        'author_key' => 'helpful-author',
        'title' => 'A useful topic from an available author account',
    ]);
    ForumBlock::factory()->create([
        'user_key' => 'mia-carter',
        'blocked_author_key' => 'blocked-author',
    ]);

    $this->get(route('forum.index'))
        ->assertOk()
        ->assertDontSee('A noisy topic')
        ->assertSee('A useful topic');
});

test('similar topic endpoint returns published matches only', function () {
    ForumTopic::factory()->create([
        'title' => 'How to help a dog enter the lift calmly',
        'body' => 'The dog became worried after a loud sound beside the doors.',
        'category' => 'behavior',
    ]);
    ForumTopic::factory()->create([
        'title' => 'Private lift training notes for our family',
        'body' => 'Private details.',
        'category' => 'behavior',
        'visibility' => ForumVisibility::Private,
    ]);
    ForumTopic::factory()->create([
        'title' => 'Draft lift training plan',
        'body' => 'Unpublished details.',
        'category' => 'behavior',
        'status' => ForumTopicStatus::Draft,
    ]);

    $this->getJson(route('forum.topics.similar', [
        'q' => 'dog enter the lift',
        'category' => 'behavior',
    ]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'How to help a dog enter the lift calmly')
        ->assertJsonMissing(['title' => 'Private lift training notes for our family']);
});
