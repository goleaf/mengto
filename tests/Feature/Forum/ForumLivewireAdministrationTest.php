<?php

declare(strict_types=1);

use App\Actions\SubmitForumModerationAppeal;
use App\Actions\SubmitForumReport;
use App\Enums\CredentialStatus;
use App\Livewire\Forum\AdminDashboard;
use App\Livewire\Forum\AnimalTaxonomySelector;
use App\Livewire\Forum\ModerationOperations;
use App\Models\Credential;
use App\Models\CredentialVerificationEvent;
use App\Models\ExpertProfile;
use App\Models\ForumCategory;
use App\Models\ForumCategoryTranslation;
use App\Models\ForumModerationAction;
use App\Models\ForumModerationActionDefinition;
use App\Models\ForumModerationAppeal;
use App\Models\ForumModerationCase;
use App\Models\ForumReport;
use App\Models\ForumTopic;
use App\Models\Taxon;
use App\Models\TaxonName;
use App\Models\User;
use Database\Seeders\ForumSystemSeeder;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(ForumSystemSeeder::class);
});

test('taxonomy selector searches a bounded result set and stores only selected ids', function () {
    $dog = Taxon::query()
        ->where('stable_key', 'taxon.core.canis-lupus-familiaris')
        ->firstOrFail();

    Livewire::test(AnimalTaxonomySelector::class)
        ->set('search', 'domestic dog')
        ->assertSee('Domestic dog')
        ->assertSee('Canis lupus familiaris')
        ->call('selectTaxon', $dog->id)
        ->assertSet('selectedTaxonIds', [$dog->id])
        ->assertSet('search', '')
        ->assertSeeHtml('name="taxon_ids[]"')
        ->assertSeeHtml('value="'.$dog->id.'"');
});

test('taxonomy selector redirects synonym selection to the accepted taxon', function () {
    $accepted = Taxon::query()
        ->where('stable_key', 'taxon.core.canis-lupus-familiaris')
        ->firstOrFail();
    $synonym = Taxon::factory()->create([
        'accepted_taxon_id' => $accepted->id,
        'resolution_status' => 'synonym',
    ]);
    TaxonName::factory()->create([
        'taxon_id' => $synonym->id,
        'name' => 'Domestic canine',
        'normalized_name' => 'domestic canine',
        'name_type' => 'synonym',
    ]);

    Livewire::test(AnimalTaxonomySelector::class)
        ->set('search', 'domestic canine')
        ->assertSee('Synonym')
        ->call('selectTaxon', $synonym->id)
        ->assertSet('selectedTaxonIds', [$accepted->id]);
});

test('taxonomy selector enforces a maximum of five selections server side', function () {
    $taxa = Taxon::factory()->count(6)->create();

    Livewire::test(AnimalTaxonomySelector::class, [
        'selected' => $taxa->take(5)->modelKeys(),
    ])
        ->call('selectTaxon', $taxa->last()->id)
        ->assertHasErrors('selectedTaxonIds')
        ->assertSet('selectedTaxonIds', $taxa->take(5)->modelKeys());
});

test('taxonomy selector supports one locked singular form field', function () {
    $taxa = Taxon::factory()->count(2)->create();

    $component = Livewire::test(AnimalTaxonomySelector::class, [
        'selected' => $taxa->first()->id,
        'inputName' => 'taxon_id',
        'selectionLimit' => 1,
    ])
        ->assertSet('inputName', 'taxon_id')
        ->assertSet('selectionLimit', 1)
        ->assertSet('selectedTaxonIds', [$taxa->first()->id])
        ->assertSeeHtml('name="taxon_id"')
        ->call('selectTaxon', $taxa->last()->id)
        ->assertSet('selectedTaxonIds', [$taxa->last()->id]);

    expect(fn () => $component->set('inputName', 'owner_id'))
        ->toThrow(CannotUpdateLockedPropertyException::class);
});

test('taxonomy selector rejects unsupported form field configuration', function () {
    Livewire::test(AnimalTaxonomySelector::class, [
        'inputName' => 'owner_id',
        'selectionLimit' => 99,
    ])
        ->assertSet('inputName', 'taxon_ids[]')
        ->assertSet('selectionLimit', 5);
});

test('forum administration is unavailable to ordinary members', function () {
    $this->get(route('admin.forum.index'))->assertForbidden();

    Livewire::actingAs($this->authenticatedUser)
        ->test(AdminDashboard::class)
        ->assertForbidden();

    Livewire::actingAs($this->authenticatedUser)
        ->test(ModerationOperations::class)
        ->assertForbidden();
});

test('administrator can edit a localized category without changing its stable key', function () {
    $administrator = User::factory()->administrator()->create();
    $category = ForumCategory::query()
        ->where('stable_key', 'forum.health')
        ->firstOrFail();
    $stableKey = $category->stable_key;

    $this->actingAs($administrator)
        ->get(route('admin.forum.index'))
        ->assertOk()
        ->assertSee('Forum administration');

    Livewire::actingAs($administrator)
        ->test(AdminDashboard::class)
        ->assertSee('Category registry')
        ->call('selectCategory', $category->id)
        ->set('translationName', 'Animal health and veterinary preparation')
        ->set('visibility', 'members')
        ->set('moderationLevel', 'high-risk')
        ->call('saveCategory')
        ->assertHasNoErrors()
        ->assertSee('Category settings were saved.');

    expect($category->refresh())
        ->stable_key->toBe($stableKey)
        ->visibility->toBe('members')
        ->moderation_level->toBe('high-risk')
        ->and(ForumCategoryTranslation::query()
            ->where('forum_category_id', $category->id)
            ->where('locale', 'en')
            ->value('name'))
        ->toBe('Animal health and veterinary preparation');
});

test('administrator credential review uses the authorized audited action', function () {
    $administrator = User::factory()->administrator()->create();
    $owner = User::factory()->create();
    $profile = ExpertProfile::factory()->unverified()->create([
        'owner_id' => $owner->id,
        'owner_key' => $owner->actor_key,
        'public_name' => 'Independent Review Candidate',
    ]);
    $credential = Credential::factory()->submitted()->create([
        'expert_profile_id' => $profile->id,
    ]);

    Livewire::actingAs($administrator)
        ->test(AdminDashboard::class)
        ->set('tab', 'verification')
        ->assertSee('Independent Review Candidate')
        ->call('selectCredential', $credential->id)
        ->set('credentialTargetStatus', CredentialStatus::InReview->value)
        ->set(
            'verificationInternalReason',
            'The credential entered an independent issuing-authority review.',
        )
        ->call('reviewCredential')
        ->assertHasNoErrors()
        ->assertSee('The credential review was recorded.');

    expect($credential->refresh()->status)->toBe(CredentialStatus::InReview)
        ->and($credential->reviewer_user_id)->toBe($administrator->id)
        ->and(CredentialVerificationEvent::query()
            ->where('credential_id', $credential->id)
            ->where('event_type', 'status-changed')
            ->exists())->toBeTrue();
});

test('administrator can triage assign and action a report without exposing its reporter', function () {
    $administrator = User::factory()->administrator()->create();
    $assignee = User::factory()->administrator()->create();
    $reportedAuthor = User::factory()->create();
    $topic = ForumTopic::factory()->create([
        'author_id' => $reportedAuthor->id,
        'author_key' => $reportedAuthor->actor_key,
    ]);
    $report = app(SubmitForumReport::class)->handle(
        reporter: $this->authenticatedUser,
        subject: $topic,
        reasonKey: 'misinformation',
        details: 'The cited evidence contradicts the claim and requires human review.',
        truthfulnessConfirmed: true,
    );
    $warning = ForumModerationActionDefinition::query()
        ->where('stable_key', 'warning')
        ->firstOrFail();

    $component = Livewire::actingAs($administrator)
        ->test(ModerationOperations::class)
        ->assertSee('Unassigned reports')
        ->assertSee('Misinformation')
        ->assertDontSee($this->authenticatedUser->name)
        ->call('openReport', $report->id)
        ->assertHasNoErrors()
        ->assertSee('The moderation case was opened.');

    $case = ForumModerationCase::query()->firstOrFail();

    $component
        ->set('assigneeUserId', $assignee->id)
        ->call('assignCase')
        ->assertHasNoErrors()
        ->assertSee('The moderation case was assigned.')
        ->set('actionDefinitionId', $warning->id)
        ->set('actionRuleId', 'forum-safety.001')
        ->set('actionPolicyBasis', 'community-safety')
        ->set(
            'actionInternalReason',
            'Independent review confirmed the warning is proportionate to the evidence.',
        )
        ->call('applyModerationAction')
        ->assertHasNoErrors()
        ->assertSee('The moderation action was recorded.');

    expect($case->refresh())
        ->assigned_to_user_id->toBe($assignee->id)
        ->status->toBe('actioned')
        ->and($report->refresh()->status)->toBe('actioned')
        ->and(ForumModerationAction::query()->count())->toBe(1);
});

test('assigned moderator can recuse through the administration component', function () {
    $moderator = User::factory()->administrator()->create();
    $report = app(SubmitForumReport::class)->handle(
        reporter: $this->authenticatedUser,
        subject: ForumTopic::factory()->create(),
        reasonKey: 'moderator-conflict',
        details: 'A documented relationship creates a reasonable appearance of conflict.',
        truthfulnessConfirmed: true,
    );

    $component = Livewire::actingAs($moderator)
        ->test(ModerationOperations::class)
        ->call('openReport', $report->id)
        ->set('assigneeUserId', $moderator->id)
        ->call('assignCase');

    $case = ForumModerationCase::query()->firstOrFail();

    $component
        ->set('recusalReason', 'connected-party')
        ->set(
            'recusalPrivateNote',
            'The assigned moderator has a private connection requiring recusal.',
        )
        ->call('recuseFromCase')
        ->assertHasNoErrors()
        ->assertSee('Your recusal was recorded and the case was unassigned.');

    expect($case->refresh())
        ->assigned_to_user_id->toBeNull()
        ->status->toBe('awaiting-review')
        ->and(ForumReport::query()->findOrFail($report->id)->status)->toBe('awaiting-review');
});

test('moderation operations reject inactive assignees and action definitions', function () {
    $administrator = User::factory()->administrator()->create();
    $inactiveAdministrator = User::factory()->administrator()->blocked()->create();
    $inactiveDefinition = ForumModerationActionDefinition::query()
        ->where('stable_key', 'warning')
        ->firstOrFail();
    $inactiveDefinition->forceFill(['is_active' => false])->save();
    $report = app(SubmitForumReport::class)->handle(
        reporter: $this->authenticatedUser,
        subject: ForumTopic::factory()->create(),
        reasonKey: 'spam',
        details: 'Repeated promotional content requires a normal human review.',
        truthfulnessConfirmed: true,
    );

    Livewire::actingAs($administrator)
        ->test(ModerationOperations::class)
        ->call('openReport', $report->id)
        ->set('assigneeUserId', $inactiveAdministrator->id)
        ->call('assignCase')
        ->assertHasErrors('assigneeUserId')
        ->set('actionDefinitionId', $inactiveDefinition->id)
        ->set('actionRuleId', 'forum-spam.001')
        ->set('actionPolicyBasis', 'community-rules')
        ->set(
            'actionInternalReason',
            'The inactive definition must never be applied through a browser mutation.',
        )
        ->call('applyModerationAction')
        ->assertHasErrors('actionDefinitionId');

    expect(ForumModerationAction::query()->count())->toBe(0)
        ->and(ForumModerationCase::query()->firstOrFail()->assigned_to_user_id)->toBeNull();
});

test('independent administrator can reverse an appeal through the review queue', function () {
    $originalModerator = User::factory()->administrator()->create();
    $independentReviewer = User::factory()->administrator()->create();
    $appellant = User::factory()->create();
    $case = ForumModerationCase::factory()->create([
        'opened_by_user_id' => $originalModerator->id,
        'status' => 'actioned',
    ]);
    $warning = ForumModerationActionDefinition::query()
        ->where('stable_key', 'warning')
        ->firstOrFail();
    $action = ForumModerationAction::factory()->create([
        'forum_moderation_case_id' => $case->id,
        'forum_moderation_action_definition_id' => $warning->id,
        'actor_user_id' => $originalModerator->id,
        'target_user_id' => $appellant->id,
        'appeal_available' => true,
    ]);
    $appeal = app(SubmitForumModerationAppeal::class)->handle(
        $appellant,
        $action,
        'The evidence belongs to another account and should be reviewed independently.',
    );

    Livewire::actingAs($independentReviewer)
        ->test(ModerationOperations::class)
        ->assertSee($appellant->name)
        ->call('selectAppeal', $appeal->id)
        ->set('appealOutcome', 'reversed')
        ->set(
            'appealDecisionReason',
            'Independent review confirmed that the evidence belongs to another account.',
        )
        ->call('reviewAppeal')
        ->assertHasNoErrors()
        ->assertSee('The appeal decision was recorded.');

    expect(ForumModerationAppeal::query()->findOrFail($appeal->id))
        ->status->toBe('reversed')
        ->reviewer_user_id->toBe($independentReviewer->id)
        ->and($action->refresh()->reversed_at)->not->toBeNull()
        ->and($case->refresh()->status)->toBe('resolved');
});

test('topic creation validates and persists selected taxonomy relations', function () {
    $dog = Taxon::query()
        ->where('stable_key', 'taxon.core.canis-lupus-familiaris')
        ->firstOrFail();

    $this->post(route('forum.topics.store'), [
        'type' => 'question',
        'category' => 'behavior',
        'taxon_ids' => [$dog->id],
        'animal_context' => 'taxa',
        'title' => 'How should I prepare a calm first training session?',
        'body' => 'I want to prepare a short and predictable first training session with clear breaks and no pressure on the animal.',
        'visibility' => 'public',
        'comment_policy' => 'registered',
        'language' => 'en',
        'intent' => 'publish',
    ])->assertRedirect();

    $topic = ForumTopic::query()->firstOrFail();

    expect($topic->taxa()->pluck('taxa.id')->all())->toBe([$dog->id])
        ->and($topic->structured_data['animal_context'])->toBe('taxa');
});
