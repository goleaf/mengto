<?php

declare(strict_types=1);

use App\Enums\ForumVisibility;
use App\Enums\KnowledgeStatus;
use App\Models\CareJournal;
use App\Models\ForumAnswer;
use App\Models\ForumTopic;
use App\Models\KnowledgeArticle;
use App\Models\SmartDevice;
use App\Models\User;

test('private forum topic is visible only to its owner or an administrator', function () {
    $topic = ForumTopic::factory()->create([
        'author_key' => 'owner-a',
        'visibility' => ForumVisibility::Private,
    ]);
    $owner = User::factory()->create(['actor_key' => 'owner-a']);
    $stranger = User::factory()->create(['actor_key' => 'owner-b']);
    $administrator = User::factory()->administrator()->create(['actor_key' => 'admin']);

    expect($owner->can('view', $topic))->toBeTrue()
        ->and($stranger->can('view', $topic))->toBeFalse()
        ->and($administrator->can('view', $topic))->toBeTrue();
});

test('care journal ownership uses the authenticated actor key', function () {
    $journal = CareJournal::factory()->create(['owner_key' => 'owner-a']);
    $owner = User::factory()->create(['actor_key' => 'owner-a']);
    $stranger = User::factory()->create(['actor_key' => 'owner-b']);

    expect($owner->can('view', $journal))->toBeTrue()
        ->and($owner->can('share', $journal))->toBeTrue()
        ->and($stranger->can('view', $journal))->toBeFalse()
        ->and($stranger->can('share', $journal))->toBeFalse();
});

test('blocked administrators do not bypass policies', function () {
    $journal = CareJournal::factory()->create(['owner_key' => 'owner-a']);
    $blockedAdministrator = User::factory()
        ->administrator()
        ->blocked()
        ->create(['actor_key' => 'blocked-admin']);

    expect($blockedAdministrator->can('view', $journal))->toBeFalse();
});

test('private forum mutations cannot be invoked by another authenticated user', function () {
    $topic = ForumTopic::factory()->create([
        'author_key' => 'private-owner',
        'visibility' => ForumVisibility::Private,
    ]);
    $stranger = User::factory()->create(['actor_key' => 'private-stranger']);

    $this->actingAs($stranger)
        ->post(route('forum.actions'), [
            'action' => 'toggle-bookmark',
            'topic_id' => $topic->id,
        ])
        ->assertForbidden();
});

test('locked forum topics reject new answers and comments', function () {
    $topic = ForumTopic::factory()->create([
        'author_key' => 'topic-owner',
        'is_locked' => true,
    ]);
    $answer = ForumAnswer::factory()->for($topic, 'topic')->create();
    $member = User::factory()->create(['actor_key' => 'forum-member']);

    $this->actingAs($member)
        ->post(route('forum.answers.store', $topic), [
            'body' => 'A sufficiently detailed answer that should be rejected because the topic is locked.',
            'experience_type' => 'personal-experience',
        ])
        ->assertForbidden();

    $this->actingAs($member)
        ->post(route('forum.comments.store', $topic), [
            'answer_id' => $answer->id,
            'body' => 'This comment must not be stored.',
        ])
        ->assertForbidden();
});

test('corrections are accepted only for visible knowledge articles', function () {
    $article = KnowledgeArticle::factory()->create(['status' => KnowledgeStatus::Draft]);
    $member = User::factory()->create(['actor_key' => 'knowledge-member']);

    $this->actingAs($member)
        ->post(route('knowledge.corrections.store', $article), [
            'field' => 'body',
            'suggestion' => 'This draft suggestion must not bypass article visibility.',
        ])
        ->assertForbidden();
});

test('inactive users cannot connect a smart device', function () {
    $blocked = User::factory()->blocked()->create(['actor_key' => 'blocked-device-owner']);

    $this->actingAs($blocked)
        ->post(route('devices.store'), [
            'name' => 'Blocked device',
            'type' => 'gps-tracker',
            'pet_profile_keys' => ['scout'],
            'ownership_confirmed' => '1',
            'privacy_acknowledged' => '1',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHas('feedback');

    expect(SmartDevice::query()->where('owner_key', 'blocked-device-owner')->exists())->toBeFalse();
});
