<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CreateForumJournalData;
use App\Enums\ForumJournalStatus;
use App\Enums\ForumJournalType;
use App\Enums\ForumTopicStatus;
use App\Enums\ForumTopicType;
use App\Enums\ForumVisibility;
use App\Models\ForumCategory;
use App\Models\ForumJournal;
use App\Models\ForumTopic;
use App\Models\ForumTopicType as ForumTopicTypeModel;
use App\Models\User;
use App\Services\ForumJournalAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class CreateForumJournal
{
    public function __construct(
        private Gate $gate,
        private ForumJournalAudit $audit,
    ) {}

    public function handle(
        User $actor,
        CreateForumJournalData $data,
    ): ForumJournal {
        $this->gate->forUser($actor)->authorize('create', ForumJournal::class);
        $this->validate($data);

        $existing = ForumJournal::query()
            ->where('creation_idempotency_key', $data->idempotencyKey)
            ->first();

        if ($existing !== null) {
            if (! $existing->isOwnedBy($actor)) {
                throw ValidationException::withMessages([
                    'journalForm.title' => __('forum_journals.validation.idempotency_conflict'),
                ]);
            }

            return $existing->load('topic');
        }

        return DB::transaction(function () use ($actor, $data): ForumJournal {
            $category = ForumCategory::query()
                ->select(['id', 'stable_key', 'slug'])
                ->active()
                ->where('slug', $data->categoryKey)
                ->firstOrFail();
            $topicTypeId = ForumTopicTypeModel::query()
                ->where('stable_key', ForumTopicType::Journal->value)
                ->where('is_active', true)
                ->value('id');

            if (! is_int($topicTypeId)) {
                throw ValidationException::withMessages([
                    'journalForm.type' => __('forum_journals.validation.topic_type'),
                ]);
            }

            $now = now();
            $topic = ForumTopic::query()->create([
                'author_id' => $actor->id,
                'author_key' => $actor->actor_key,
                'author_name' => $actor->name,
                'author_initials' => $this->initials($actor->name),
                'author_role' => null,
                'slug' => Str::slug($data->title).'-'.Str::lower(Str::random(6)),
                'type' => ForumTopicType::Journal,
                'title' => trim($data->title),
                'body' => trim($data->body),
                'category' => $category->slug,
                'forum_category_id' => $category->id,
                'forum_topic_type_id' => $topicTypeId,
                'tags' => ['journal', $data->type->value],
                'status' => ForumTopicStatus::Published,
                'visibility' => $data->visibility,
                'comment_policy' => 'registered',
                'language' => $data->locale,
                'media' => [],
                'is_urgent' => false,
                'is_medical' => false,
                'is_locked' => false,
                'has_expert_answer' => false,
                'view_count' => 0,
                'last_activity_at' => $now,
                'published_at' => $now,
                'structured_data' => [
                    'journal_type' => $data->type->value,
                    'started_on' => $data->startedOn->toDateString(),
                ],
                'structured_data_version' => 1,
                'lock_version' => 1,
            ]);

            $journal = ForumJournal::query()->create([
                'forum_topic_id' => $topic->id,
                'owner_user_id' => $actor->id,
                'owner_key' => $actor->actor_key,
                'stable_key' => 'journal-'.Str::lower((string) Str::ulid()),
                'creation_idempotency_key' => $data->idempotencyKey,
                'type' => $data->type,
                'status' => ForumJournalStatus::Active,
                'started_on' => $data->startedOn,
                'timezone' => $data->timezone,
                'lock_version' => 0,
            ]);

            $this->audit->record($journal, $actor, 'forum-journal.created', [
                'journal_type' => $data->type->value,
                'visibility' => $data->visibility->value,
            ]);

            return $journal->load('topic');
        }, 3);
    }

    private function validate(CreateForumJournalData $data): void
    {
        Validator::make([
            'title' => $data->title,
            'body' => $data->body,
            'category' => $data->categoryKey,
            'type' => $data->type->value,
            'visibility' => $data->visibility->value,
            'started_on' => $data->startedOn->toDateString(),
            'timezone' => $data->timezone,
            'locale' => $data->locale,
            'idempotency_key' => $data->idempotencyKey,
        ], [
            'title' => ['required', 'string', 'min:5', 'max:180'],
            'body' => ['required', 'string', 'min:10', 'max:10000'],
            'category' => [
                'required',
                'string',
                Rule::exists('forum_categories', 'slug')
                    ->where('is_active', true)
                    ->whereNull('archived_at'),
            ],
            'type' => ['required', Rule::enum(ForumJournalType::class)],
            'visibility' => [
                'required',
                Rule::in([
                    ForumVisibility::Public->value,
                    ForumVisibility::Members->value,
                    ForumVisibility::Experts->value,
                    ForumVisibility::Link->value,
                    ForumVisibility::Private->value,
                ]),
            ],
            'started_on' => ['required', 'date', 'after_or_equal:1900-01-01', 'before_or_equal:tomorrow'],
            'timezone' => ['required', 'timezone:all'],
            'locale' => ['required', Rule::in(config('platform.supported_locales', ['en']))],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();
    }

    private function initials(string $name): string
    {
        return Str::of($name)
            ->split('/\s+/')
            ->filter()
            ->take(2)
            ->map(static fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');
    }
}
