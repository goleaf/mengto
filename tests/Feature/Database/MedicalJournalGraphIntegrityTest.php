<?php

declare(strict_types=1);

use App\Enums\ForumJournalStatus;
use App\Enums\ForumJournalType;
use App\Enums\ForumTopicStatus;
use App\Enums\ForumTopicType;
use App\Models\ForumJournal;
use App\Models\MedicalRecord;
use App\Models\PetProfile;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RepresentativeDomainSeeder;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\seed;

test('the default medical record factory creates one coherent canonical pet graph', function () {
    $record = MedicalRecord::factory()->create();
    $profile = $record->petProfile()->with('user')->firstOrFail();

    expect($record->pet_profile_id)->toBe($profile->id)
        ->and($record->owner_id)->toBe($profile->user_id)
        ->and($record->owner_key)->toBe($profile->user->actor_key)
        ->and($record->pet_profile_key)->toBe($profile->slug)
        ->and($record->pet_name)->toBe($profile->name)
        ->and($record->species)->toBe(strtolower($profile->species))
        ->and($record->breed)->toBe($profile->breed)
        ->and($record->birth_date?->toDateString())->toBe($profile->birth_date?->toDateString())
        ->and($record->sex)->toBe($profile->sex)
        ->and($record->reproductive_status)->toBe($profile->reproductive_status)
        ->and($record->image_url)->toBe(
            $profile->profile_data['profile_image'] ?? $profile->profile_data['avatar'] ?? null,
        );
});

test('the medical record factory exposes an explicit petless legacy state', function () {
    $owner = User::factory()->create();

    $record = MedicalRecord::factory()->legacy($owner)->create();

    expect($record->pet_profile_id)->toBeNull()
        ->and($record->petProfile()->exists())->toBeFalse()
        ->and($record->owner_id)->toBe($owner->id)
        ->and($record->owner_key)->toBe($owner->actor_key);
});

test('canonical medical records reject an owner who does not own the pet profile', function () {
    $profile = PetProfile::factory()->create();
    $otherOwner = User::factory()->create();

    expect(fn () => MedicalRecord::factory()->create([
        'pet_profile_id' => $profile->id,
        'owner_id' => $otherOwner->id,
        'owner_key' => $otherOwner->actor_key,
    ]))->toThrow(
        LogicException::class,
        'Canonical medical record factories require the pet profile owner identity.',
    );
});

test('the forum journal factory creates a dedicated journal topic with one owner identity', function () {
    $journal = ForumJournal::factory()->create();
    $topic = $journal->topic()->with('author')->firstOrFail();

    expect($topic->type)->toBe(ForumTopicType::Journal)
        ->and($topic->author_id)->not->toBeNull()
        ->and($journal->owner_user_id)->toBe($topic->author_id)
        ->and($journal->owner_key)->toBe($topic->author_key)
        ->and($topic->author?->actor_key)->toBe($journal->owner_key)
        ->and($topic->author?->name)->toBe($topic->author_name)
        ->and($topic->structured_data['journal_type'] ?? null)->toBe($journal->type->value)
        ->and($topic->structured_data['started_on'] ?? null)->toBe($journal->started_on->toDateString());
});

test('each forum journal type keeps its dedicated topic payload coherent', function (ForumJournalType $type) {
    $journal = ForumJournal::factory()->withType($type)->create();

    expect($journal->type)->toBe($type)
        ->and($journal->topic->type)->toBe(ForumTopicType::Journal)
        ->and($journal->topic->structured_data['journal_type'] ?? null)->toBe($type->value)
        ->and($journal->topic->structured_data['started_on'] ?? null)
        ->toBe($journal->started_on->toDateString());
})->with(collect(ForumJournalType::cases())->mapWithKeys(
    static fn (ForumJournalType $type): array => [$type->value => $type],
));

test('the archived journal factory archives and locks its backing topic', function () {
    $actor = User::factory()->create();
    $journal = ForumJournal::factory()->archived($actor)->create()->load('topic');

    expect($journal->status)->toBe(ForumJournalStatus::Archived)
        ->and($journal->archived_by_user_id)->toBe($actor->id)
        ->and($journal->metadata['pre_archive_topic_status'] ?? null)
        ->toBe(ForumTopicStatus::Published->value)
        ->and($journal->topic->status)->toBe(ForumTopicStatus::Archived)
        ->and($journal->topic->is_locked)->toBeTrue()
        ->and($journal->topic->archived_at)->not->toBeNull();
});

test('representative seeding preserves bounded coherent medical and forum journal graphs', function () {
    Storage::fake('local');
    $this->authenticatedUser->forceFill(['email' => 'user@example.com'])->save();
    seed(DatabaseSeeder::class);

    $medicalCount = MedicalRecord::query()->count();
    $journalCount = ForumJournal::query()->count();

    expect($medicalCount)->toBeGreaterThanOrEqual(10)
        ->and($journalCount)->toBeGreaterThanOrEqual(10);

    MedicalRecord::query()
        ->with('petProfile.user')
        ->orderBy('id')
        ->each(function (MedicalRecord $record): void {
            $profile = $record->petProfile;

            expect($profile)->not->toBeNull()
                ->and($record->owner_id)->toBe($profile->user_id)
                ->and($record->owner_key)->toBe($profile->user->actor_key)
                ->and($record->pet_profile_key)->toBe($profile->slug)
                ->and($record->pet_name)->toBe($profile->name)
                ->and($record->species)->toBe(strtolower($profile->species))
                ->and($record->breed)->toBe($profile->breed);
        });

    ForumJournal::query()
        ->with('topic.author')
        ->orderBy('id')
        ->each(function (ForumJournal $journal): void {
            expect($journal->topic->type)->toBe(ForumTopicType::Journal)
                ->and($journal->owner_user_id)->toBe($journal->topic->author_id)
                ->and($journal->owner_key)->toBe($journal->topic->author_key)
                ->and($journal->topic->author?->actor_key)->toBe($journal->owner_key);
        });

    seed(RepresentativeDomainSeeder::class);

    expect(MedicalRecord::query()->count())->toBe($medicalCount)
        ->and(ForumJournal::query()->count())->toBe($journalCount);
});
