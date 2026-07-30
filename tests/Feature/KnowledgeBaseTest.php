<?php

use App\Enums\KnowledgeStatus;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCorrection;
use App\Models\KnowledgeVersion;

test('knowledge library exposes reviewed articles but not editorial drafts', function () {
    $published = KnowledgeArticle::factory()->create([
        'title' => 'A reviewed carrier confidence guide',
        'category' => 'behavior',
    ]);
    KnowledgeArticle::factory()->create([
        'title' => 'An internal carrier editorial draft',
        'category' => 'behavior',
        'status' => KnowledgeStatus::Review,
        'published_at' => null,
    ]);

    $this->get(route('pet-social.knowledge.index', [
        'q' => 'carrier',
        'category' => 'behavior',
        'type' => 'all',
    ]))
        ->assertOk()
        ->assertSee($published->title)
        ->assertDontSee('An internal carrier editorial draft');
});

test('article page presents sources version history and accepts corrections', function () {
    $article = KnowledgeArticle::factory()->create([
        'slug' => 'safe-cat-carrier-routine',
        'title' => 'Safe cat carrier routine',
        'sources' => ['https://catvets.com/resource/feline-behavior-guidelines/'],
        'contributors' => [
            ['name' => 'Sofia Arden', 'role' => 'expert reviewer'],
        ],
    ]);
    KnowledgeVersion::factory()->create([
        'article_id' => $article->id,
        'title' => $article->title,
        'body' => $article->body,
        'change_summary' => 'Initial expert review.',
    ]);

    $this->get(route('pet-social.knowledge.articles.show', $article))
        ->assertOk()
        ->assertSee($article->title)
        ->assertSee('Sofia Arden')
        ->assertSee('Initial expert review')
        ->assertSee('catvets.com');

    $this->post(route('pet-social.knowledge.corrections.store', $article), [
        'field' => 'body',
        'suggestion' => 'Clarify that the door should move only after the cat remains relaxed.',
        'source_url' => 'https://catvets.com/resource/feline-behavior-guidelines/',
    ])
        ->assertRedirect(route('pet-social.knowledge.articles.show', $article))
        ->assertSessionHas('pawcircle.feedback');

    expect(KnowledgeCorrection::query()->firstOrFail())
        ->article_id->toBe($article->id)
        ->reporter_key->toBe('mia-carter')
        ->status->toBe('submitted');
});

test('unpublished knowledge article is not publicly readable', function () {
    $article = KnowledgeArticle::factory()->create([
        'status' => KnowledgeStatus::Review,
        'published_at' => null,
    ]);

    $this->get(route('pet-social.knowledge.articles.show', $article))->assertNotFound();
});
