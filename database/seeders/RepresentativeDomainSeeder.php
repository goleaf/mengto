<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\DeviceCommandStatus;
use App\Enums\ForumEventSessionRole;
use App\Enums\ForumEventVerificationStatus;
use App\Enums\ForumJournalType;
use App\Enums\ForumMentorshipState;
use App\Enums\ForumTopicType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PetBreedOriginType;
use App\Enums\PetSizeCategory;
use App\Enums\SocialRelationshipDirection;
use App\Enums\SocialRelationshipType;
use App\Enums\SocialRequestStatus;
use App\Models\Booking;
use App\Models\Consultation;
use App\Models\ContentMediaAsset;
use App\Models\ContentPublication;
use App\Models\DeviceAutomationRun;
use App\Models\DeviceCommand;
use App\Models\ExpertReport;
use App\Models\ForumAnswer;
use App\Models\ForumCategory;
use App\Models\ForumEvent;
use App\Models\ForumEventRegistration;
use App\Models\ForumEventRegistrationPet;
use App\Models\ForumEventRoom;
use App\Models\ForumEventSession;
use App\Models\ForumEventSessionStaff;
use App\Models\ForumGroup;
use App\Models\ForumJournal;
use App\Models\ForumMentorship;
use App\Models\ForumMentorshipFeedback;
use App\Models\ForumModerationCase;
use App\Models\ForumPoll;
use App\Models\ForumPollVote;
use App\Models\ForumReport;
use App\Models\ForumTopic;
use App\Models\ForumTopicAcceptance;
use App\Models\ForumTopicType as ForumTopicTypeModel;
use App\Models\ForumUserTrustLevel;
use App\Models\ListingReport;
use App\Models\ListingReview;
use App\Models\MedicalRecord;
use App\Models\Order;
use App\Models\PetProfile;
use App\Models\PetProfileFact;
use App\Models\Review;
use App\Models\SearchCase;
use App\Models\SearchContactRelay;
use App\Models\SearchReport;
use App\Models\SearchTask;
use App\Models\Service;
use App\Models\Sighting;
use App\Models\SocialAccountBlock;
use App\Models\SocialActor;
use App\Models\SocialActorSetting;
use App\Models\SocialRelationship;
use App\Models\SocialRelationshipRequest;
use App\Models\Taxon;
use App\Models\TaxonExternalIdentifier;
use App\Models\TaxonSource;
use App\Models\TaxonVersion;
use App\Models\User;
use App\Services\SocialRelationshipKey;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use LogicException;

final class RepresentativeDomainSeeder extends Seeder
{
    use WithoutModelEvents;

    /** @var array<class-string<Model>, EloquentCollection<int, Model>> */
    private array $recyclePools = [];

    /** @var array<string, class-string<Model>> */
    private array $modelsByTable = [];

    public function run(): void
    {
        $allowedEnvironments = config('platform.demo_seed_environments');

        if (! is_array($allowedEnvironments) || ! app()->environment($allowedEnvironments)) {
            throw new LogicException('Representative seed data may only be created in an explicitly allowed environment.');
        }

        $this->initializePools();

        foreach (RepresentativeModelManifest::classes() as $modelClass) {
            if ($modelClass === User::class) {
                continue;
            }

            $this->topUpModel($modelClass);
        }

        $this->synchronizeCanonicalMedicalRecords();
        $this->populateRepresentativeNullableFields();
        $this->seedPivots();
    }

    private function synchronizeCanonicalMedicalRecords(): void
    {
        $this->scopeDemoModel(MedicalRecord::query())
            ->with('petProfile.user:id,actor_key')
            ->whereNotNull('pet_profile_id')
            ->lazyById()
            ->each(function (MedicalRecord $record): void {
                $profile = $record->petProfile;

                if (! $profile instanceof PetProfile) {
                    return;
                }

                $record->forceFill([
                    'owner_id' => $profile->user_id,
                    'owner_key' => $profile->user->actor_key,
                    'pet_profile_key' => $profile->slug,
                    'pet_name' => $profile->name,
                    'species' => Str::lower($profile->species),
                    'breed' => $profile->breed,
                    'image_url' => $profile->profile_data['profile_image']
                        ?? $profile->profile_data['avatar']
                        ?? null,
                ]);

                if ($record->isDirty()) {
                    $record->save();
                }
            });
    }

    private function initializePools(): void
    {
        foreach (RepresentativeModelManifest::classes() as $modelClass) {
            $model = new $modelClass;
            $this->modelsByTable[$model->getTable()] = $modelClass;
            $this->refreshPool($modelClass);
        }
    }

    /** @param class-string<Model> $modelClass */
    private function topUpModel(string $modelClass): void
    {
        $missing = max(
            0,
            RepresentativeModelManifest::TARGET_COUNT - $modelClass::query()->count(),
        );

        for ($position = 0; $position < $missing; $position++) {
            $factory = Factory::factoryForModel($modelClass)->recycle(
                $this->recycleModels($this->uniqueParentClasses($modelClass), $position),
            );
            $overrides = $this->unusedUniqueForeignKeys($modelClass, $position);
            $model = $factory->state($overrides)->create();

            $this->remember($model);
        }

        $this->refreshPool($modelClass);
    }

    /**
     * Allocate deterministic users for one-to-one relationships. Non-user
     * unique parents are excluded from recycling and are therefore created as
     * fresh aggregate roots by their relationship factories.
     *
     * @param  class-string<Model>  $modelClass
     * @return array<string, mixed>
     */
    private function unusedUniqueForeignKeys(string $modelClass, int $position): array
    {
        $model = new $modelClass;
        $table = $model->getTable();
        $foreignKeys = collect(Schema::getForeignKeys($table));
        $overrides = $this->domainSpecificOverrides($modelClass, $position);

        foreach (Schema::getIndexes($table) as $index) {
            $columns = $index['columns'] ?? [];

            if (($index['unique'] ?? false) !== true || $columns === []) {
                continue;
            }

            if (count($columns) !== 1) {
                continue;
            }

            $column = $columns[0];

            if (array_key_exists($column, $overrides)) {
                continue;
            }

            $foreignKey = $foreignKeys->first(
                static fn (array $key): bool => ($key['columns'] ?? []) === [$column],
            );

            if (! is_array($foreignKey)) {
                continue;
            }

            $foreignTable = $foreignKey['foreign_table'] ?? null;
            $foreignColumn = $foreignKey['foreign_columns'][0] ?? null;
            $parentClass = is_string($foreignTable)
                ? ($this->modelsByTable[$foreignTable] ?? null)
                : null;

            if (! is_string($parentClass) || ! is_string($foreignColumn)) {
                continue;
            }

            $parentQuery = $parentClass === User::class
                ? $this->demoUserQuery()
                : $parentClass::query();
            $parent = $parentQuery
                ->whereNotIn(
                    $foreignColumn,
                    $modelClass::query()->whereNotNull($column)->select($column),
                )
                ->orderBy($foreignColumn)
                ->first();

            if (! $parent instanceof Model) {
                if ($parentClass === User::class) {
                    throw new LogicException("No unused deterministic user remains for {$table}.{$column}.");
                }

                $parent = Factory::factoryForModel($parentClass)
                    ->recycle($this->recycleModels($this->uniqueParentClasses($parentClass)))
                    ->create();
                $this->remember($parent);
            }

            $overrides[$column] = $parent->getAttribute($foreignColumn);
        }

        return $overrides;
    }

    /**
     * Preserve invariants that cannot be inferred from foreign keys alone.
     *
     * @param  class-string<Model>  $modelClass
     * @return array<string, mixed>
     */
    private function domainSpecificOverrides(string $modelClass, int $position): array
    {
        if ($modelClass === Consultation::class && $position === 0) {
            return [
                'status' => 'completed',
                'started_at' => now()->subHours(2),
                'ended_at' => now()->subHour(),
                'referral_summary' => 'Routine follow-up with the primary veterinarian if symptoms change.',
                'summary_confirmed_at' => now()->subHour(),
            ];
        }

        if ($modelClass === DeviceAutomationRun::class && $position === 0) {
            return [
                'status' => 'failed',
                'is_simulation' => false,
                'error' => 'The representative device was offline during delivery.',
                'result' => ['delivered' => false, 'retryable' => true],
            ];
        }

        if ($modelClass === DeviceCommand::class && $position === 0) {
            return [
                'status' => DeviceCommandStatus::Failed,
                'completed_at' => null,
                'failure_reason' => 'The representative device did not acknowledge the command.',
                'result' => ['acknowledged' => false],
            ];
        }

        if ($modelClass === DeviceCommand::class && $position === 1) {
            return [
                'requires_confirmation' => true,
                'confirmed_at' => now()->subMinute(),
                'expires_at' => now()->addMinutes(10),
            ];
        }

        if ($modelClass === ForumEventRoom::class && $position === 0) {
            return [
                'is_online' => true,
                'online_url' => 'https://example.test/events/representative-room',
                'public_directions' => 'Join from the event schedule after registration.',
                'exact_directions' => 'The private room link appears after approval.',
            ];
        }

        if ($modelClass === ForumMentorship::class && $position === 0) {
            $validator = $this->demoUserQuery()
                ->where('is_admin', true)
                ->orderBy('id')
                ->firstOrFail();

            return [
                'state' => ForumMentorshipState::Completed,
                'accepted_at' => now()->subWeeks(2),
                'mentor_safety_acknowledged_at' => now()->subWeeks(2),
                'completed_at' => now()->subDay(),
                'ended_at' => now()->subDay(),
                'completion_validated_at' => now(),
                'validated_by_user_id' => $validator->id,
                'ended_by_user_id' => $validator->id,
                'end_reason' => 'Representative goals were completed and acknowledged.',
                'open_key' => null,
            ];
        }

        if ($modelClass === ForumMentorship::class && $position === 1) {
            return [
                'state' => ForumMentorshipState::Declined,
                'declined_at' => now(),
                'ended_at' => now(),
                'end_reason' => 'The requested scope was not a suitable match.',
                'open_key' => null,
            ];
        }

        if ($modelClass === Order::class && $position === 0) {
            return [
                'status' => OrderStatus::Completed,
                'payment_status' => PaymentStatus::Paid,
                'paid_at' => now()->subDays(2),
                'completed_at' => now()->subDay(),
            ];
        }

        if ($modelClass === Order::class && $position === 1) {
            return [
                'status' => OrderStatus::Cancelled,
                'payment_status' => PaymentStatus::Cancelled,
                'cancelled_at' => now()->subDay(),
            ];
        }

        if ($modelClass === SearchTask::class && $position === 0) {
            $assignee = User::query()->where('actor_key', 'demo-volunteer')->firstOrFail();

            return [
                'status' => 'completed',
                'assignee_key' => $assignee->actor_key,
                'assignee_name' => $assignee->name,
                'claimed_at' => now()->subHours(2),
                'completed_at' => now()->subHour(),
                'result' => 'The assigned public sector was checked without a confirmed sighting.',
            ];
        }

        if ($modelClass === ListingReview::class && $position === 0) {
            return [
                'seller_reply' => 'Thank you for documenting the handover clearly.',
                'replied_at' => now(),
            ];
        }

        if ($modelClass === Review::class && $position === 0) {
            return [
                ...$this->reviewOverrides(),
                'expert_reply' => 'Thank you for sharing how the plan worked for your household.',
                'replied_at' => now(),
            ];
        }

        if ($modelClass === TaxonExternalIdentifier::class && $position === 0) {
            return ['external_url' => 'https://example.test/taxa/representative-record'];
        }

        if ($modelClass === MedicalRecord::class) {
            $profile = $this->scopeDemoModel(PetProfile::query())
                ->with('user:id,actor_key')
                ->whereDoesntHave('medicalRecord')
                ->orderBy('id')
                ->first();

            if (! $profile instanceof PetProfile) {
                $profile = PetProfile::factory()
                    ->recycle($this->recycleModels([PetProfile::class]))
                    ->create();
                $profile->load('user:id,actor_key');
                $this->remember($profile);
            }

            return [
                'owner_id' => $profile->user_id,
                'pet_profile_id' => $profile->id,
                'owner_key' => $profile->user->actor_key,
                'pet_profile_key' => $profile->slug,
                'pet_name' => $profile->name,
                'species' => Str::lower($profile->species),
                'breed' => $profile->breed,
                'birth_date' => $profile->birth_date,
                'sex' => $profile->sex,
                'reproductive_status' => $profile->reproductive_status,
                'image_url' => $profile->profile_data['profile_image']
                    ?? $profile->profile_data['avatar']
                    ?? null,
            ];
        }

        if ($modelClass === ForumJournal::class) {
            $position = ForumJournal::query()->count();
            $types = ForumJournalType::cases();
            $type = $types[$position % count($types)];
            $startedOn = now()->subDays($position)->toDateString();
            $owners = $this->demoUsers();
            $owner = $owners[$position % $owners->count()];
            $topicTypeId = ForumTopicTypeModel::query()
                ->where('stable_key', ForumTopicType::Journal->value)
                ->where('is_active', true)
                ->valueOrFail('id');
            $initials = Str::of($owner->name)
                ->split('/\s+/')
                ->filter()
                ->take(2)
                ->map(static fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))
                ->implode('');
            $topic = ForumTopic::factory()->state([
                'author_id' => $owner->id,
                'author_key' => $owner->actor_key,
                'author_name' => $owner->name,
                'author_initials' => $initials,
                'author_role' => null,
                'forum_topic_type_id' => $topicTypeId,
                'type' => ForumTopicType::Journal,
                'title' => "Representative {$type->value} journal {$position}",
                'structured_data' => [
                    'journal_type' => $type->value,
                    'started_on' => $startedOn,
                ],
                'structured_data_version' => 1,
                'lock_version' => 1,
            ])->create();
            $this->remember($topic);

            return [
                'forum_topic_id' => $topic->id,
                'owner_user_id' => $owner->id,
                'owner_key' => $owner->actor_key,
                'type' => $type,
                'started_on' => $startedOn,
            ];
        }

        if ($modelClass === Review::class) {
            return $this->reviewOverrides();
        }

        if ($modelClass === ExpertReport::class) {
            $review = Review::query()
                ->whereNotNull('booking_id')
                ->orderBy('id')
                ->first();

            if ($review instanceof Review) {
                return [
                    'expert_profile_id' => $review->expert_profile_id,
                    'booking_id' => $review->booking_id,
                    'review_id' => $review->id,
                    'reporter_key' => $review->reviewer_key,
                ];
            }
        }

        if ($modelClass === ListingReport::class) {
            $reporter = User::query()->where('email', 'user@example.com')->firstOrFail();

            return [
                'reporter_id' => $reporter->id,
                'reporter_key' => $reporter->actor_key,
            ];
        }

        if ($modelClass === ForumReport::class) {
            $affectedUser = User::query()->where('email', 'user@example.com')->firstOrFail();
            $affectedPet = PetProfile::query()
                ->where('user_id', $affectedUser->id)
                ->orderBy('id')
                ->first();

            return [
                'affected_user_id' => $affectedUser->id,
                'affected_pet_profile_id' => $affectedPet?->id,
            ];
        }

        if ($modelClass === SocialActorSetting::class) {
            $actor = SocialActor::query()
                ->whereDoesntHave('settings')
                ->orderBy('id')
                ->first();

            if (! $actor instanceof SocialActor) {
                $pet = PetProfile::factory()
                    ->recycle($this->recycleModels())
                    ->create();
                $actor = SocialActor::factory()->forPet($pet)->create();
                $this->remember($pet);
                $this->remember($actor);
            }

            return ['social_actor_id' => $actor->id];
        }

        if ($modelClass === ForumMentorshipFeedback::class) {
            $mentorship = ForumMentorship::query()
                ->whereDoesntHave('feedback')
                ->orderBy('id')
                ->firstOrFail();

            return ['forum_mentorship_id' => $mentorship->id];
        }

        if ($modelClass === ForumTopicAcceptance::class) {
            $answer = ForumAnswer::query()
                ->whereDoesntHave(
                    'acceptances',
                    static fn ($query) => $query->where('acceptance_type', 'author'),
                )
                ->orderBy('id')
                ->first();

            if (! $answer instanceof ForumAnswer) {
                $answer = ForumAnswer::factory()
                    ->recycle($this->recycleModels())
                    ->create();
                $this->remember($answer);
            }

            return [
                'forum_topic_id' => $answer->topic_id,
                'forum_answer_id' => $answer->id,
                'acceptance_type' => 'author',
            ];
        }

        if ($modelClass === ForumUserTrustLevel::class) {
            $user = $this->demoUserQuery()
                ->whereDoesntHave('forumTrustAssignments', function ($query): void {
                    $query
                        ->where('scope_type', 'global')
                        ->where('scope_key', 'global');
                })
                ->orderBy('id')
                ->firstOrFail();
            $administrator = $this->demoUserQuery()
                ->where('is_admin', true)
                ->orderBy('id')
                ->firstOrFail();

            return [
                'user_id' => $user->id,
                'granted_by_user_id' => $administrator->id,
                'scope_type' => 'global',
                'scope_key' => 'global',
            ];
        }

        if ($modelClass === PetProfileFact::class) {
            $variants = [
                'birth-date' => [
                    'value' => ['date' => now()->subYears(4)->toDateString()],
                    'precision' => 'exact',
                ],
                'breed' => [
                    'value' => ['label' => 'Mixed breed', 'classification' => 'owner-reported'],
                    'precision' => 'estimated',
                ],
                'microchip-record' => [
                    'value' => [
                        'status' => 'chipped',
                        'identifier' => '981020000000001',
                        'documents_state' => 'available',
                    ],
                    'precision' => 'exact',
                ],
            ];

            $profiles = $this->scopeDemoModel(PetProfile::query())
                ->orderBy('id')
                ->limit(10)
                ->get();

            foreach ($profiles as $profile) {
                foreach ($variants as $key => $variant) {
                    $currentKey = "pet:{$profile->id}:fact:{$key}";

                    if (! PetProfileFact::query()->where('current_key', $currentKey)->exists()) {
                        return [
                            'pet_profile_id' => $profile->id,
                            'author_user_id' => $profile->user_id,
                            'fact_key' => $key,
                            'value' => $variant['value'],
                            'normalized_value_hash' => hash(
                                'sha256',
                                json_encode($variant['value'], JSON_THROW_ON_ERROR),
                            ),
                            'precision' => $variant['precision'],
                            'current_key' => $currentKey,
                        ];
                    }
                }
            }

            $profile = PetProfile::factory()
                ->recycle($this->recycleModels())
                ->create();
            $this->remember($profile);
            $value = $variants['birth-date']['value'];

            return [
                'pet_profile_id' => $profile->id,
                'author_user_id' => $profile->user_id,
                'fact_key' => 'birth-date',
                'value' => $value,
                'normalized_value_hash' => hash('sha256', json_encode($value, JSON_THROW_ON_ERROR)),
                'precision' => 'exact',
                'current_key' => "pet:{$profile->id}:fact:birth-date",
            ];
        }

        if ($modelClass === PetProfile::class) {
            $categories = PetSizeCategory::cases();
            $position = PetProfile::query()->count();

            return [
                'breed_origin_type' => PetBreedOriginType::Single,
                'size_category' => $categories[$position % count($categories)],
            ];
        }

        if ($modelClass === SearchCase::class) {
            $sequence = SearchCase::query()->count() + 1;
            $name = "Representative Pet {$sequence}";
            $profileKey = "representative-pet-{$sequence}";

            return [
                'pet_name' => $name,
                'pet_profile_key' => $profileKey,
                'animal_snapshot' => [
                    'name' => $name,
                    'species' => 'dog',
                    'breed' => 'Mixed breed',
                    'captured_at' => now()->toIso8601String(),
                ],
            ];
        }

        if ($modelClass === SearchContactRelay::class) {
            $case = SearchCase::query()
                ->whereNotNull('owner_id')
                ->orderBy('id')
                ->firstOrFail();
            $sender = $this->demoUserQuery()
                ->whereKeyNot($case->owner_id)
                ->orderBy('id')
                ->firstOrFail();

            return [
                'search_case_id' => $case->id,
                'sender_user_id' => $sender->id,
                'recipient_user_id' => $case->owner_id,
            ];
        }

        if ($modelClass === SearchReport::class) {
            $reporter = User::query()->where('email', 'user@example.com')->firstOrFail();
            $case = SearchCase::query()
                ->where('slug', 'scout-missing-vingis-park')
                ->firstOrFail();
            $sighting = Sighting::query()
                ->where('search_case_id', $case->id)
                ->orderBy('id')
                ->first();
            $forumReport = ForumReport::query()
                ->where('subject_type', SearchCase::class)
                ->where('subject_id', (string) $case->id)
                ->orderBy('id')
                ->first();

            if (! $forumReport instanceof ForumReport) {
                $forumReport = ForumReport::factory()
                    ->forSubject($case)
                    ->create(['reporter_id' => $reporter->id]);
                $this->remember($forumReport);
            }

            return [
                'search_case_id' => $case->id,
                'sighting_id' => $sighting?->id,
                'forum_report_id' => $forumReport->id,
                'reporter_id' => $reporter->id,
                'reporter_key' => $reporter->actor_key,
            ];
        }

        if ($modelClass === SocialAccountBlock::class) {
            $users = $this->demoUsers();

            foreach ($users as $blocker) {
                foreach ($users as $blocked) {
                    if ($blocker->is($blocked)) {
                        continue;
                    }

                    $activeKey = hash('sha256', "{$blocker->id}|{$blocked->id}");

                    if (! SocialAccountBlock::query()->where('active_key', $activeKey)->exists()) {
                        return [
                            'blocker_user_id' => $blocker->id,
                            'blocked_user_id' => $blocked->id,
                            'created_by_user_id' => $blocker->id,
                            'active_key' => $activeKey,
                        ];
                    }
                }
            }
        }

        if ($modelClass === SocialRelationship::class) {
            $actors = SocialActor::query()->orderBy('id')->limit(10)->get();

            foreach ($actors as $source) {
                foreach ($actors as $target) {
                    if ($source->is($target)) {
                        continue;
                    }

                    $activeKey = SocialRelationshipKey::forRelationship(
                        $source->id,
                        $target->id,
                        SocialRelationshipType::Follow,
                    );

                    if (! SocialRelationship::query()->where('active_key', $activeKey)->exists()) {
                        return [
                            'source_actor_id' => $source->id,
                            'target_actor_id' => $target->id,
                            'relationship_type' => SocialRelationshipType::Follow,
                            'active_key' => $activeKey,
                        ];
                    }
                }
            }
        }

        if ($modelClass === SocialRelationshipRequest::class) {
            $actors = SocialActor::query()->orderBy('id')->limit(10)->get();

            foreach ($actors as $source) {
                foreach ($actors as $target) {
                    if ($source->is($target)) {
                        continue;
                    }

                    $activeKey = SocialRelationshipKey::forRequest(
                        $source->id,
                        $target->id,
                        SocialRelationshipType::OwnerFriendship,
                    );

                    if (! SocialRelationshipRequest::query()->where('active_key', $activeKey)->exists()) {
                        return [
                            'source_actor_id' => $source->id,
                            'target_actor_id' => $target->id,
                            'relationship_type' => SocialRelationshipType::OwnerFriendship,
                            'active_key' => $activeKey,
                        ];
                    }
                }
            }
        }

        if ($modelClass === ForumPollVote::class) {
            $poll = ForumPoll::query()
                ->whereDoesntHave('votes')
                ->orderBy('id')
                ->firstOrFail();

            return ['forum_poll_id' => $poll->id];
        }

        if ($modelClass === ForumEventSessionStaff::class) {
            $sessions = ForumEventSession::query()->orderBy('id')->limit(10)->get();
            $users = $this->demoUsers();

            foreach ($sessions as $session) {
                foreach ($users as $user) {
                    foreach (ForumEventSessionRole::cases() as $role) {
                        $exists = ForumEventSessionStaff::query()
                            ->where('forum_event_session_id', $session->id)
                            ->where('user_id', $user->id)
                            ->where('role', $role->value)
                            ->exists();

                        if (! $exists) {
                            return [
                                'forum_event_session_id' => $session->id,
                                'user_id' => $user->id,
                                'role' => $role,
                            ];
                        }
                    }
                }
            }
        }

        if ($modelClass === ForumEventRegistrationPet::class) {
            $position = ForumEventRegistrationPet::query()->count();
            $registrations = $this->scopeDemoModel(ForumEventRegistration::query())
                ->with('user:id')
                ->orderBy('id')
                ->get();

            foreach ($registrations as $registration) {
                $pet = PetProfile::query()
                    ->where('user_id', $registration->user_id)
                    ->whereNotIn(
                        'id',
                        ForumEventRegistrationPet::query()
                            ->where('forum_event_registration_id', $registration->id)
                            ->select('pet_profile_id'),
                    )
                    ->orderBy('id')
                    ->first();

                if (! $pet instanceof PetProfile) {
                    $pet = PetProfile::factory()->for($registration->user)->create();
                    $this->remember($pet);
                }

                return [
                    'forum_event_registration_id' => $registration->id,
                    'pet_profile_id' => $pet->id,
                    'eligibility_status' => ForumEventVerificationStatus::Confirmed,
                    'verification_source' => ForumEventVerificationStatus::ReportedByParticipant,
                    'conditions' => $position === 0
                        ? 'Use a quiet entrance and allow additional settling time.'
                        : 'No special participation conditions reported.',
                    'checked_in_at' => $position === 0 ? now()->subHour() : null,
                    'checked_out_at' => $position === 0 ? now() : null,
                ];
            }
        }

        return [];
    }

    /** @return array<string, mixed> */
    private function reviewOverrides(): array
    {
        $booking = Booking::query()
            ->with('service:id,expert_profile_id')
            ->where('status', BookingStatus::Completed->value)
            ->whereNotNull('completed_at')
            ->whereDoesntHave('review')
            ->orderBy('id')
            ->first();

        if (! $booking instanceof Booking) {
            $service = Service::query()
                ->select(['id', 'expert_profile_id'])
                ->orderBy('id')
                ->firstOrFail();
            $client = $this->demoUserQuery()->orderBy('id')->firstOrFail();
            $booking = Booking::factory()->completed()->create([
                'expert_profile_id' => $service->expert_profile_id,
                'service_id' => $service->id,
                'client_id' => $client->id,
                'client_key' => $client->actor_key,
            ]);
            $booking->setRelation('service', $service);
            $this->remember($booking);
        }

        return [
            'expert_profile_id' => $booking->service->expert_profile_id,
            'service_id' => $booking->service_id,
            'booking_id' => $booking->id,
        ];
    }

    /**
     * @param  list<class-string<Model>>  $excludedClasses
     * @return Collection<int, Model>
     */
    private function recycleModels(array $excludedClasses = [], int $position = 0): Collection
    {
        return collect($this->recyclePools)
            ->except($excludedClasses)
            ->flatMap(static function (EloquentCollection $models) use ($position): array {
                if ($models->isEmpty()) {
                    return [];
                }

                return [$models->values()[$position % $models->count()]];
            })
            ->values();
    }

    /**
     * Children whose foreign keys participate in any unique index receive a
     * fresh non-user aggregate parent so their factory can preserve
     * participant and lifecycle semantics. Users remain recycled from the
     * deterministic ten-account pool and single-column one-to-one user keys
     * are allocated explicitly above.
     *
     * @param  class-string<Model>  $modelClass
     * @return list<class-string<Model>>
     */
    private function uniqueParentClasses(string $modelClass): array
    {
        return $this->collectUniqueParentClasses($modelClass, []);
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  list<class-string<Model>>  $visited
     * @return list<class-string<Model>>
     */
    private function collectUniqueParentClasses(string $modelClass, array $visited): array
    {
        if (in_array($modelClass, $visited, true)) {
            return [];
        }

        $visited[] = $modelClass;
        $model = new $modelClass;
        $table = $model->getTable();
        $foreignKeys = collect(Schema::getForeignKeys($table));
        $parents = [];

        foreach (Schema::getIndexes($table) as $index) {
            $columns = $index['columns'] ?? [];

            if (($index['unique'] ?? false) !== true || $columns === []) {
                continue;
            }

            $indexParents = collect($columns)
                ->map(function (string $column) use ($foreignKeys): ?string {
                    $foreignKey = $foreignKeys->first(
                        static fn (array $key): bool => ($key['columns'] ?? []) === [$column],
                    );
                    $foreignTable = is_array($foreignKey)
                        ? ($foreignKey['foreign_table'] ?? null)
                        : null;

                    return is_string($foreignTable)
                        ? ($this->modelsByTable[$foreignTable] ?? null)
                        : null;
                })
                ->filter(static fn (?string $parentClass): bool => is_string($parentClass) && $parentClass !== User::class)
                ->values();

            if ($indexParents->isEmpty()) {
                continue;
            }

            if (count($columns) === 1) {
                $parents[] = $indexParents->first();

                continue;
            }

            $parents[] = $indexParents->first(
                fn (string $parentClass): bool => ! $this->hasUniqueUserForeignKey($parentClass),
            ) ?? $indexParents->first();
        }

        foreach (array_values(array_unique($parents)) as $parentClass) {
            $parents = [
                ...$parents,
                ...$this->collectUniqueParentClasses($parentClass, $visited),
            ];
        }

        return array_values(array_unique($parents));
    }

    /** @param class-string<Model> $modelClass */
    private function hasUniqueUserForeignKey(string $modelClass): bool
    {
        $model = new $modelClass;
        $table = $model->getTable();
        $foreignKeys = collect(Schema::getForeignKeys($table));

        foreach (Schema::getIndexes($table) as $index) {
            $columns = $index['columns'] ?? [];

            if (($index['unique'] ?? false) !== true || count($columns) !== 1) {
                continue;
            }

            $foreignKey = $foreignKeys->first(
                static fn (array $key): bool => ($key['columns'] ?? []) === [$columns[0]],
            );
            $foreignTable = is_array($foreignKey)
                ? ($foreignKey['foreign_table'] ?? null)
                : null;

            if (is_string($foreignTable) && ($this->modelsByTable[$foreignTable] ?? null) === User::class) {
                return true;
            }
        }

        return false;
    }

    /** @param class-string<Model> $modelClass */
    private function refreshPool(string $modelClass): void
    {
        $query = $this->scopeDemoModel((new $modelClass)->newQuery());

        $this->recyclePools[$modelClass] = $query
            ->orderBy((new $modelClass)->getKeyName())
            ->limit(RepresentativeModelManifest::TARGET_COUNT)
            ->get();
    }

    /** @return Builder<User> */
    private function demoUserQuery(): Builder
    {
        return $this->scopeDemoUsers(User::query());
    }

    /**
     * Keep representative reuse away from records tied to non-demo accounts.
     * Tables without a direct users foreign key remain shared reference or
     * aggregate-child pools; their user-owned parents are selected from the
     * scoped pools resolved here.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private function scopeDemoModel(Builder $query): Builder
    {
        $model = $query->getModel();

        if ($model instanceof User) {
            return $query->where(static function (Builder $demoQuery): void {
                $demoQuery
                    ->where('actor_key', 'mia-carter')
                    ->orWhere('actor_key', 'like', 'demo-%');
            });
        }

        if ($model instanceof SocialActor) {
            return $query->whereIn(
                'user_id',
                $this->demoUserQuery()->select('id'),
            );
        }

        $userColumns = collect(Schema::getForeignKeys($model->getTable()))
            ->filter(static fn (array $foreignKey): bool => ($foreignKey['foreign_table'] ?? null) === 'users')
            ->flatMap(static fn (array $foreignKey): array => $foreignKey['columns'] ?? [])
            ->filter(static fn (mixed $column): bool => is_string($column))
            ->values();

        foreach ($userColumns as $column) {
            $query->where(function (Builder $userScope) use ($column): void {
                $userScope
                    ->whereNull($column)
                    ->orWhereIn($column, $this->demoUserQuery()->select('id'));
            });
        }

        return $query;
    }

    /** @param Builder<User> $query
     * @return Builder<User>
     */
    private function scopeDemoUsers(Builder $query): Builder
    {
        return $query->where(static function (Builder $demoQuery): void {
            $demoQuery
                ->where('actor_key', 'mia-carter')
                ->orWhere('actor_key', 'like', 'demo-%');
        });
    }

    /** @return EloquentCollection<int, User> */
    private function demoUsers(): EloquentCollection
    {
        return $this->demoUserQuery()->orderBy('id')->limit(10)->get();
    }

    private function remember(Model $model): void
    {
        $modelClass = $model::class;
        $pool = $this->recyclePools[$modelClass] ?? new EloquentCollection;

        if ($pool->count() < RepresentativeModelManifest::TARGET_COUNT) {
            $pool->push($model);
            $this->recyclePools[$modelClass] = $pool;
        }
    }

    private function seedPivots(): void
    {
        $this->seedContentMedia();
        $this->seedCategoryRelations();
        $this->seedTaxonRelations();
        $this->seedModerationReports();
    }

    private function populateRepresentativeNullableFields(): void
    {
        $source = TaxonSource::query()
            ->where('stable_key', 'platform-core-animal-taxonomy')
            ->whereNotNull('active_taxon_import_id')
            ->firstOrFail();
        $version = TaxonVersion::query()
            ->where('source_record_id', 'taxon.core.canis-lupus-familiaris')
            ->where('taxon_source_id', $source->id)
            ->where('taxon_import_id', $source->active_taxon_import_id)
            ->firstOrFail();

        $version->forceFill([
            'authorship' => 'Representative taxonomy authority, 2026',
        ])->save();

        $this->seedDeterministicDeclinedSocialRequest();

        $representativePet = PetProfile::query()
            ->whereHas('user', static fn ($query) => $query->where('email', 'user@example.com'))
            ->where('profile_key', 'pet-scout')
            ->first();

        if ($representativePet instanceof PetProfile) {
            $representativePet->forceFill([
                'breed_origin_type' => $representativePet->breed_origin_type ?? PetBreedOriginType::Unknown,
                'size_category' => $representativePet->size_category ?? PetSizeCategory::Medium,
            ])->save();
        }
    }

    private function seedDeterministicDeclinedSocialRequest(): void
    {
        $sourceUser = User::query()->where('actor_key', 'mia-carter')->firstOrFail();
        $targetUser = User::query()->where('actor_key', 'demo-lithuanian')->firstOrFail();
        $source = SocialActor::query()->where('user_id', $sourceUser->id)->firstOrFail();
        $target = SocialActor::query()->where('user_id', $targetUser->id)->firstOrFail();
        $request = SocialRelationshipRequest::query()->firstOrNew([
            'request_key' => '50000000-0000-4000-8000-000000000001',
        ]);

        $request->forceFill([
            'source_actor_id' => $source->id,
            'target_actor_id' => $target->id,
            'relationship_type' => SocialRelationshipType::TemporaryContact,
            'direction' => SocialRelationshipDirection::Directed,
            'status' => SocialRequestStatus::Declined,
            'active_key' => null,
            'idempotency_key' => 'representative:declined-social-request:v1',
            'created_by_user_id' => $sourceUser->id,
            'decided_by_user_id' => $sourceUser->id,
            'context_type' => 'representative-seed',
            'context_key' => 'declined-social-request',
            'message' => 'Representative declined temporary-contact request.',
            'message_fingerprint' => hash('sha256', 'representative declined temporary-contact request'),
            'risk_level' => 'normal',
            'risk_signals' => ['representative-lifecycle'],
            'reason_code' => 'not-needed',
            'lock_version' => 1,
            'metadata' => ['representative_seed' => true],
            'sent_at' => now()->subDays(2),
            'delivered_at' => now()->subDays(2)->addMinute(),
            'decided_at' => now()->subDay(),
            'expires_at' => now()->addDays(28),
            'repeat_after' => now()->addDays((int) config('social_relationships.repeat_cooldown_days', 30)),
            'prevent_repeats' => false,
        ])->save();
    }

    private function seedContentMedia(): void
    {
        $publications = $this->scopeDemoModel(ContentPublication::query())->orderBy('id')->limit(10)->get();
        $assets = $this->scopeDemoModel(ContentMediaAsset::query())->orderBy('id')->limit(10)->get();

        foreach ($publications as $position => $publication) {
            $asset = $assets[$position];
            $publication->mediaAssets()->syncWithoutDetaching([
                $asset->id => [
                    'position' => 1,
                    'is_cover' => true,
                    'caption' => "Representative media {$position}",
                ],
            ]);
        }
    }

    private function seedCategoryRelations(): void
    {
        $categories = ForumCategory::query()->orderBy('id')->limit(11)->get();

        for ($position = 0; $position < 10; $position++) {
            $categories[$position]->relatedCategories()->syncWithoutDetaching([
                $categories[$position + 1]->id => [
                    'relation_type' => 'related',
                    'position' => $position + 1,
                ],
            ]);
        }
    }

    private function seedTaxonRelations(): void
    {
        $taxa = Taxon::query()
            ->with('activeVersion:id,taxon_id,scientific_name')
            ->orderBy('id')
            ->limit(10)
            ->get();
        $events = $this->scopeDemoModel(ForumEvent::query())->orderBy('id')->limit(10)->get();
        $groups = $this->scopeDemoModel(ForumGroup::query())->orderBy('id')->limit(10)->get();
        $topics = $this->scopeDemoModel(ForumTopic::query())->orderBy('id')->limit(10)->get();

        for ($position = 0; $position < 10; $position++) {
            $taxon = $taxa[$position];
            $events[$position]->taxa()->syncWithoutDetaching([
                $taxon->id => ['is_primary' => $position === 0],
            ]);
            $groups[$position]->taxa()->syncWithoutDetaching([
                $taxon->id => ['is_primary' => $position === 0],
            ]);
            $topics[$position]->taxa()->syncWithoutDetaching([
                $taxon->id => [
                    'context_type' => 'subject',
                    'topic_time_snapshot' => json_encode([
                        'scientific_name' => $taxon->activeVersion->scientific_name ?? $taxon->stable_key,
                        'captured_by' => 'representative-domain-seeder',
                    ], JSON_THROW_ON_ERROR),
                ],
            ]);
        }
    }

    private function seedModerationReports(): void
    {
        $cases = $this->scopeDemoModel(ForumModerationCase::query())->orderBy('id')->limit(10)->get();
        $reports = $this->scopeDemoModel(ForumReport::query())->orderBy('id')->limit(10)->get();
        $administrator = $this->demoUserQuery()
            ->where('is_admin', true)
            ->orderBy('id')
            ->firstOrFail();

        foreach ($cases as $position => $case) {
            $case->reports()->syncWithoutDetaching([
                $reports[$position]->id => [
                    'linked_by_user_id' => $administrator->id,
                    'created_at' => now(),
                ],
            ]);
        }
    }
}
