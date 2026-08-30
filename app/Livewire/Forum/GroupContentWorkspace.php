<?php

declare(strict_types=1);

namespace App\Livewire\Forum;

use App\Actions\AssociateForumTopicWithGroup;
use App\Actions\AssociateKnowledgeGuideWithGroup;
use App\Actions\CastForumPollVote;
use App\Actions\CreateForumGroupActivity;
use App\Actions\CreateForumPoll;
use App\Actions\PublishForumGroupAnnouncement;
use App\Actions\StoreForumGroupFile;
use App\Data\CastForumPollVoteData;
use App\Enums\ForumGroupActivityFormat;
use App\Enums\ForumGroupFileStatus;
use App\Enums\ForumPollEligibility;
use App\Enums\ForumPollResultVisibility;
use App\Enums\ForumPollStatus;
use App\Enums\ForumPollType;
use App\Enums\ForumPollVoterVisibility;
use App\Enums\ForumTopicStatus;
use App\Enums\KnowledgeStatus;
use App\Livewire\Forms\ForumGroupActivityForm;
use App\Livewire\Forms\ForumGroupAnnouncementForm;
use App\Livewire\Forms\ForumGroupAssociationForm;
use App\Livewire\Forms\ForumPollForm;
use App\Models\ForumGroup;
use App\Models\ForumGroupActivity;
use App\Models\ForumGroupAnnouncement;
use App\Models\ForumGroupFile;
use App\Models\ForumPoll;
use App\Models\ForumPollVote;
use App\Models\ForumTopic;
use App\Models\KnowledgeArticle;
use App\Models\User;
use App\Services\ForumPollEligibility as ForumPollEligibilityService;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

final class GroupContentWorkspace extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $groupId;

    public ForumGroupAssociationForm $association;

    public ForumGroupActivityForm $activity;

    public ForumGroupAnnouncementForm $announcement;

    public ForumPollForm $poll;

    public ?TemporaryUploadedFile $groupFile = null;

    public string $groupFileDescription = '';

    /** @var array<int, int|string|list<int|string>> */
    public array $pollChoices = [];

    /** @var array<int, string> */
    public array $voteTokens = [];

    /** @var array<int, int> */
    public array $voteVersions = [];

    #[Locked]
    public string $topicToken;

    #[Locked]
    public string $guideToken;

    #[Locked]
    public string $activityToken;

    #[Locked]
    public string $announcementToken;

    #[Locked]
    public string $pollToken;

    #[Locked]
    public string $fileToken;

    public string $feedback = '';

    private ForumPollEligibilityService $pollEligibility;

    public function boot(ForumPollEligibilityService $pollEligibility): void
    {
        $this->pollEligibility = $pollEligibility;
    }

    public function mount(int $groupId): void
    {
        $this->groupId = $groupId;
        $group = $this->groupModel();
        Gate::authorize('viewMemberContent', $group);
        $user = $this->requireUser();
        $this->activity->timezone = $user->timezone;
        $this->activity->locationScope = $group->location_scope ?? '';
        $this->rotateContentTokens();

        $visiblePollIds = ForumPoll::query()
            ->where('forum_group_id', $group->id)
            ->whereIn('status', [
                ForumPollStatus::Active->value,
                ForumPollStatus::Cancelled->value,
            ])
            ->whereNull('archived_at')
            ->latest('created_at')
            ->latest('id')
            ->limit(10)
            ->pluck('id');

        ForumPollVote::query()
            ->select(['id', 'forum_poll_id', 'user_id', 'choices', 'lock_version'])
            ->where('user_id', $user->id)
            ->whereIn('forum_poll_id', $visiblePollIds)
            ->get()
            ->each(function (ForumPollVote $vote): void {
                $this->pollChoices[$vote->forum_poll_id] = $vote->choices;
                $this->voteVersions[$vote->forum_poll_id] = $vote->lock_version;
                $this->voteTokens[$vote->forum_poll_id] = $this->token('vote');
            });
    }

    /** @return array<string, mixed> */
    #[Computed]
    public function content(): array
    {
        $user = $this->requireUser();
        $group = $this->groupModel();
        Gate::forUser($user)->authorize('viewMemberContent', $group);
        $timezone = $user->timezone;

        $announcements = ForumGroupAnnouncement::query()
            ->select([
                'id',
                'forum_group_id',
                'author_user_id',
                'stable_key',
                'title',
                'body',
                'published_at',
                'expires_at',
            ])
            ->with('author:id,name')
            ->where('forum_group_id', $group->id)
            ->current()
            ->latest('published_at')
            ->limit(10)
            ->get();

        $topics = ForumTopic::query()
            ->select([
                'id',
                'forum_group_id',
                'slug',
                'title',
                'author_name',
                'type',
                'status',
                'last_activity_at',
            ])
            ->where('forum_group_id', $group->id)
            ->whereIn('status', ForumTopicStatus::publicValues())
            ->latest('last_activity_at')
            ->limit(12)
            ->get();

        $activities = ForumGroupActivity::query()
            ->select([
                'id',
                'forum_group_id',
                'created_by_user_id',
                'stable_key',
                'title',
                'summary',
                'format',
                'status',
                'starts_at',
                'ends_at',
                'timezone',
                'location_scope',
                'capacity',
            ])
            ->with('creator:id,name')
            ->where('forum_group_id', $group->id)
            ->whereNull('archived_at')
            ->orderBy('starts_at')
            ->limit(12)
            ->get();

        $guides = KnowledgeArticle::query()
            ->select([
                'id',
                'forum_group_id',
                'created_by_user_id',
                'slug',
                'title',
                'summary',
                'status',
                'language',
                'updated_at',
            ])
            ->with('creator:id,name')
            ->where('forum_group_id', $group->id)
            ->whereIn('status', KnowledgeStatus::publicValues())
            ->latest('updated_at')
            ->limit(12)
            ->get();

        $files = ForumGroupFile::query()
            ->select([
                'id',
                'forum_group_id',
                'uploaded_by_user_id',
                'stable_key',
                'original_name',
                'mime_type',
                'byte_size',
                'description',
                'created_at',
            ])
            ->with('uploader:id,name')
            ->where('forum_group_id', $group->id)
            ->where('status', ForumGroupFileStatus::Active->value)
            ->latest()
            ->limit(12)
            ->get();

        $polls = ForumPoll::query()
            ->select([
                'id',
                'forum_group_id',
                'created_by_user_id',
                'stable_key',
                'question',
                'description',
                'type',
                'voter_visibility',
                'result_visibility',
                'is_vote_editable',
                'eligibility',
                'location_scope',
                'status',
                'closes_at',
                'total_vote_count',
                'lock_version',
                'archived_at',
                'created_at',
            ])
            ->with([
                'creator:id,name',
                'options:id,forum_poll_id,stable_key,label,position,selection_count,first_choice_count',
                'votes' => fn ($query) => $query
                    ->select(['id', 'forum_poll_id', 'user_id', 'choices', 'lock_version'])
                    ->where('user_id', $user->id),
            ])
            ->where('forum_group_id', $group->id)
            ->whereIn('status', [
                ForumPollStatus::Active->value,
                ForumPollStatus::Cancelled->value,
            ])
            ->whereNull('archived_at')
            ->latest('created_at')
            ->latest('id')
            ->limit(10)
            ->get();
        $hasTrustedCommunityAssignment = $this->pollEligibility
            ->hasTrustedCommunityAssignment($user);

        $visibleVoterPollIds = $polls
            ->filter(
                fn (ForumPoll $poll): bool => $poll->voter_visibility
                    === ForumPollVoterVisibility::Visible
                    && $poll->resultsAreVisibleTo($user),
            )
            ->pluck('id');
        $visibleVoters = ForumPollVote::query()
            ->select(['id', 'forum_poll_id', 'user_id', 'updated_at'])
            ->with('user:id,name')
            ->whereIn('forum_poll_id', $visibleVoterPollIds)
            ->latest('updated_at')
            ->limit(100)
            ->get()
            ->groupBy('forum_poll_id');

        return [
            'can_create' => Gate::forUser($user)->allows('createContent', $group),
            'can_publish_announcement' => Gate::forUser($user)
                ->allows('publishAnnouncement', $group),
            'announcements' => $announcements->map(fn (ForumGroupAnnouncement $item): array => [
                'id' => $item->id,
                'title' => $item->title,
                'body' => $item->body,
                'author' => $item->author->name,
                'published_at' => $this->formatDate($item->published_at, $timezone),
                'expires_at' => $item->expires_at === null
                    ? null
                    : $this->formatDate($item->expires_at, $timezone),
            ])->all(),
            'topics' => $topics->map(static fn (ForumTopic $topic): array => [
                'id' => $topic->id,
                'title' => $topic->title,
                'author' => $topic->author_name,
                'type' => $topic->type->label(),
                'status' => $topic->status->label(),
                'url' => route('forum.topics.show', $topic),
            ])->all(),
            'activities' => $activities->map(fn (ForumGroupActivity $item): array => [
                'id' => $item->id,
                'title' => $item->title,
                'summary' => $item->summary,
                'format' => $item->format->label(),
                'status' => $item->status->label(),
                'creator' => $item->creator->name,
                'starts_at' => $this->formatDate($item->starts_at, $timezone),
                'ends_at' => $this->formatDate($item->ends_at, $timezone),
                'location_scope' => $item->location_scope,
                'capacity' => $item->capacity,
            ])->all(),
            'guides' => $guides->map(static fn (KnowledgeArticle $guide): array => [
                'id' => $guide->id,
                'title' => $guide->title,
                'summary' => $guide->summary,
                'author' => $guide->creator?->name,
                'url' => route('knowledge.articles.show', $guide),
            ])->all(),
            'files' => $files->map(static fn (ForumGroupFile $file): array => [
                'id' => $file->id,
                'name' => $file->original_name,
                'description' => $file->description,
                'mime_type' => $file->mime_type,
                'size_kb' => number_format($file->byte_size / 1024, 1),
                'uploader' => $file->uploader->name,
                'url' => route('forum.groups.files.download', [$group, $file]),
            ])->all(),
            'polls' => $polls->map(function (ForumPoll $poll) use (
                $group,
                $hasTrustedCommunityAssignment,
                $timezone,
                $user,
                $visibleVoters,
            ): array {
                $currentVote = $poll->votes->isEmpty()
                    ? null
                    : $poll->votes->firstOrFail();
                $resultsVisible = $poll->resultsAreVisibleTo($user);

                return [
                    'id' => $poll->id,
                    'question' => $poll->question,
                    'description' => $poll->description,
                    'type' => $poll->type->value,
                    'type_label' => $poll->type->label(),
                    'voter_visibility' => $poll->voter_visibility->label(),
                    'is_anonymous' => $poll->voter_visibility === ForumPollVoterVisibility::Anonymous,
                    'result_visibility' => $poll->result_visibility->label(),
                    'eligibility' => $poll->eligibility->label(),
                    'is_location_limited' => $poll->eligibility === ForumPollEligibility::LocationMembers,
                    'is_vote_editable' => $poll->is_vote_editable,
                    'is_closed' => $poll->isClosed(),
                    'can_vote' => ! $poll->isClosed()
                        && $this->pollEligibility->allowsWithinAuthorizedGroup(
                            $user,
                            $poll,
                            $group,
                            $hasTrustedCommunityAssignment,
                        ),
                    'results_visible' => $resultsVisible,
                    'total_votes' => $resultsVisible ? $poll->total_vote_count : null,
                    'closes_at' => $poll->closes_at === null
                        ? null
                        : $this->formatDate($poll->closes_at, $timezone),
                    'current_choices' => $currentVote === null ? [] : $currentVote->choices,
                    'current_vote_version' => $currentVote === null
                        ? null
                        : $currentVote->lock_version,
                    'creator' => $poll->creator->name,
                    'options' => $poll->options->map(
                        static fn ($option): array => [
                            'id' => $option->id,
                            'label' => $option->label,
                            'position' => $option->position,
                            'selection_count' => $resultsVisible
                                ? $option->selection_count
                                : null,
                            'first_choice_count' => $resultsVisible
                                ? $option->first_choice_count
                                : null,
                        ],
                    )->all(),
                    'voters' => $visibleVoters->get($poll->id, collect())
                        ->map(static fn (ForumPollVote $vote): string => $vote->user->name)
                        ->unique()
                        ->values()
                        ->all(),
                ];
            })->all(),
        ];
    }

    /** @return array<string, string> */
    #[Computed]
    public function pollTypeOptions(): array
    {
        return collect(ForumPollType::cases())
            ->mapWithKeys(static fn (ForumPollType $type): array => [
                $type->value => $type->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function voterVisibilityOptions(): array
    {
        return collect(ForumPollVoterVisibility::cases())
            ->mapWithKeys(static fn (ForumPollVoterVisibility $visibility): array => [
                $visibility->value => $visibility->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function resultVisibilityOptions(): array
    {
        return collect(ForumPollResultVisibility::cases())
            ->mapWithKeys(static fn (ForumPollResultVisibility $visibility): array => [
                $visibility->value => $visibility->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function pollEligibilityOptions(): array
    {
        return collect(ForumPollEligibility::cases())
            ->mapWithKeys(static fn (ForumPollEligibility $eligibility): array => [
                $eligibility->value => $eligibility->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function activityFormatOptions(): array
    {
        return collect(ForumGroupActivityFormat::cases())
            ->mapWithKeys(static fn (ForumGroupActivityFormat $format): array => [
                $format->value => $format->label(),
            ])
            ->all();
    }

    public function linkTopic(AssociateForumTopicWithGroup $associate): void
    {
        $topic = ForumTopic::query()
            ->where('slug', $this->association->validatedTopicSlug())
            ->firstOrFail();
        $associate->handle($this->requireUser(), $this->groupModel(), $topic);
        $this->association->reset('topicSlug');
        $this->topicToken = $this->token('topic');
        $this->complete('topic_linked');
    }

    public function linkGuide(AssociateKnowledgeGuideWithGroup $associate): void
    {
        $guide = KnowledgeArticle::query()
            ->where('slug', $this->association->validatedGuideSlug())
            ->firstOrFail();
        $associate->handle($this->requireUser(), $this->groupModel(), $guide);
        $this->association->reset('guideSlug');
        $this->guideToken = $this->token('guide');
        $this->complete('guide_linked');
    }

    public function createActivity(CreateForumGroupActivity $create): void
    {
        $create->handle(
            $this->requireUser(),
            $this->groupModel(),
            $this->activity->toData($this->activityToken),
        );
        $timezone = $this->activity->timezone;
        $this->activity->reset();
        $this->activity->timezone = $timezone;
        $this->activityToken = $this->token('activity');
        $this->complete('activity_created');
    }

    public function publishAnnouncement(PublishForumGroupAnnouncement $publish): void
    {
        $publish->handle(
            $this->requireUser(),
            $this->groupModel(),
            $this->announcement->toData($this->announcementToken),
        );
        $this->announcement->reset();
        $this->announcementToken = $this->token('announcement');
        $this->complete('announcement_published');
    }

    public function createPoll(CreateForumPoll $create): void
    {
        $create->handle(
            $this->requireUser(),
            $this->groupModel(),
            $this->poll->toData($this->pollToken),
        );
        $this->poll->reset();
        $this->pollToken = $this->token('poll');
        $this->complete('poll_created');
    }

    public function uploadFile(StoreForumGroupFile $store): void
    {
        $this->validate([
            'groupFile' => [
                'required',
                'file',
                'max:10240',
                'mimetypes:application/pdf,text/plain,image/jpeg,image/png,image/webp',
            ],
            'groupFileDescription' => ['nullable', 'string', 'max:1000'],
        ]);
        $upload = $this->groupFile;
        abort_unless($upload instanceof TemporaryUploadedFile, 422);
        $store->handle(
            $this->requireUser(),
            $this->groupModel(),
            $upload,
            $this->groupFileDescription,
            $this->fileToken,
        );
        $this->reset('groupFile', 'groupFileDescription');
        $this->fileToken = $this->token('file');
        $this->complete('file_uploaded');
    }

    public function castVote(
        int $pollId,
        CastForumPollVote $cast,
    ): void {
        $poll = ForumPoll::query()
            ->where('forum_group_id', $this->groupId)
            ->findOrFail($pollId);
        $rawChoices = $this->pollChoices[$pollId] ?? [];
        $choices = is_array($rawChoices)
            ? collect($rawChoices)
                ->filter(static fn (mixed $choice): bool => is_numeric($choice))
                ->map(static fn (mixed $choice): int => (int) $choice)
                ->values()
                ->all()
            : [(int) $rawChoices];
        $token = $this->voteTokens[$pollId] ?? $this->token('vote');
        $vote = $cast->handle(
            $this->requireUser(),
            $poll,
            new CastForumPollVoteData(
                choices: $choices,
                idempotencyKey: $token,
                expectedVoteVersion: $this->voteVersions[$pollId] ?? null,
            ),
        );
        $this->pollChoices[$pollId] = $vote->choices;
        $this->voteVersions[$pollId] = $vote->lock_version;
        $this->voteTokens[$pollId] = $this->token('vote');
        $this->complete('vote_recorded');
    }

    public function render()
    {
        return view('livewire.forum.group-content-workspace');
    }

    private function complete(string $messageKey): void
    {
        unset($this->content);
        $this->feedback = __("forum_polls.feedback.{$messageKey}");
    }

    private function groupModel(): ForumGroup
    {
        return ForumGroup::query()->findOrFail($this->groupId);
    }

    private function requireUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        return $user;
    }

    private function formatDate(CarbonInterface $date, string $timezone): string
    {
        return $date->copy()->setTimezone($timezone)->translatedFormat('M j, Y H:i');
    }

    private function rotateContentTokens(): void
    {
        $this->topicToken = $this->token('topic');
        $this->guideToken = $this->token('guide');
        $this->activityToken = $this->token('activity');
        $this->announcementToken = $this->token('announcement');
        $this->pollToken = $this->token('poll');
        $this->fileToken = $this->token('file');
    }

    private function token(string $operation): string
    {
        return sprintf(
            'livewire:group-content:%d:%s:%s',
            $this->groupId,
            $operation,
            Str::lower((string) Str::uuid()),
        );
    }
}
