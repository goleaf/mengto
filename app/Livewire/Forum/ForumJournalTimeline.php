<?php

declare(strict_types=1);

namespace App\Livewire\Forum;

use App\Actions\ArchiveForumJournal;
use App\Actions\CreateForumJournalComment;
use App\Actions\CreateForumJournalEntry;
use App\Actions\GrantForumJournalCollaborator;
use App\Actions\RevokeForumJournalCollaborator;
use App\Actions\StoreForumJournalMedia;
use App\Actions\UpdateForumJournalEntry;
use App\Enums\ForumJournalCollaboratorRole;
use App\Enums\ForumJournalCollaboratorState;
use App\Enums\ForumJournalEntryKind;
use App\Livewire\Forms\ForumJournalCollaboratorForm;
use App\Livewire\Forms\ForumJournalCommentForm;
use App\Livewire\Forms\ForumJournalEntryForm;
use App\Livewire\Forms\ForumJournalMediaForm;
use App\Models\ForumJournal;
use App\Models\ForumJournalCollaborator;
use App\Models\ForumJournalEntry;
use App\Models\ForumJournalMeasurement;
use App\Models\User;
use App\Services\ForumJournalMetricRegistry;
use App\Services\LocaleFormatter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

final class ForumJournalTimeline extends Component
{
    use WithFileUploads;
    use WithPagination;

    protected LocaleFormatter $formatter;

    protected ForumJournalMetricRegistry $metrics;

    #[Locked]
    public int $journalId;

    #[Locked]
    public int $journalVersion;

    #[Locked]
    public ?int $editingEntryId = null;

    #[Locked]
    public ?int $commentEntryId = null;

    #[Locked]
    public ?int $mediaEntryId = null;

    public ForumJournalEntryForm $entryForm;

    public ForumJournalCommentForm $commentForm;

    public ForumJournalCollaboratorForm $collaboratorForm;

    public ForumJournalMediaForm $mediaForm;

    public string $feedback = '';

    public function boot(
        LocaleFormatter $formatter,
        ForumJournalMetricRegistry $metrics,
    ): void {
        $this->formatter = $formatter;
        $this->metrics = $metrics;
    }

    public function mount(int $journalId): void
    {
        $this->journalId = $journalId;
        $journal = $this->journal();
        Gate::authorize('view', $journal);
        $this->journalVersion = $journal->lock_version;
        $this->initializeEntryForm();
        $this->initializeCommentForm();
        $this->initializeMediaForm();
    }

    /** @return array<string, mixed> */
    #[Computed]
    public function journalData(): array
    {
        $journal = $this->journal([
            'topic:id,title,slug,forum_group_id,visibility,is_locked,comment_policy',
            'owner:id,name',
        ]);
        Gate::authorize('view', $journal);
        $user = Auth::user();
        $owner = $journal->getRelation('owner');

        return [
            'title' => $journal->topic->title,
            'type' => $journal->type->label(),
            'status' => $journal->status->label(),
            'started_on' => $this->formatter->date($journal->started_on),
            'owner_name' => $owner instanceof User
                ? $owner->name
                : __('forum_journals.fallback.unknown_owner'),
            'visibility' => $journal->topic->visibility->label(),
            'is_archived' => $journal->isArchived(),
            'can_update' => Gate::forUser($user)->allows('update', $journal),
            'can_comment' => Gate::forUser($user)->allows('comment', $journal),
            'can_manage_collaborators' => Gate::forUser($user)->allows('manageCollaborators', $journal),
            'can_archive' => Gate::forUser($user)->allows('archive', $journal),
            'can_export' => Gate::forUser($user)->allows('export', $journal),
            'export_url' => route('forum.journals.export', $journal),
        ];
    }

    /** @return LengthAwarePaginator<int, array<string, mixed>> */
    #[Computed]
    public function entries(): LengthAwarePaginator
    {
        $journal = $this->journal();
        Gate::authorize('view', $journal);

        return ForumJournalEntry::query()
            ->forTimeline()
            ->where('forum_journal_id', $journal->id)
            ->with([
                'measurements:id,forum_journal_entry_id,metric_key,numeric_value,unit,position',
                'comments' => fn ($comments) => $comments
                    ->forJournalEntry()
                    ->orderBy('created_at')
                    ->orderBy('id'),
                'media:id,forum_journal_entry_id,stable_key,mime_type,byte_size,alt_text,caption,status,created_at',
            ])
            ->withCount('versions')
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(10, pageName: 'journalPage')
            ->through(fn (ForumJournalEntry $entry): array => $this->presentEntry(
                $entry,
                $journal,
                $this->formatter,
            ));
    }

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function progressSeries(): array
    {
        $journal = $this->journal();
        Gate::authorize('view', $journal);
        $definitions = collect($this->metrics->definitions($journal->type))->keyBy('key');

        return ForumJournalMeasurement::query()
            ->select([
                'id',
                'forum_journal_entry_id',
                'metric_key',
                'numeric_value',
                'unit',
                'position',
            ])
            ->whereHas(
                'entry',
                fn ($entries) => $entries->where('forum_journal_id', $journal->id),
            )
            ->with('entry:id,occurred_at,title')
            ->latest('id')
            ->limit(80)
            ->get()
            ->groupBy('metric_key')
            ->map(function ($measurements, string $metricKey) use ($definitions): array {
                $definition = $definitions->get($metricKey);
                $items = $measurements
                    ->sortBy(fn ($measurement) => $measurement->entry?->occurred_at)
                    ->take(-12)
                    ->values()
                    ->map(static fn ($measurement): array => [
                        'id' => $measurement->id,
                        'value' => (float) $measurement->numeric_value,
                        'title' => $measurement->entry?->title,
                    ])
                    ->all();

                return [
                    'key' => $metricKey,
                    'label' => __("forum_journals.metrics.{$metricKey}"),
                    'unit' => __("forum_journals.units.{$measurements->first()->unit}"),
                    'min' => is_array($definition) ? $definition['min'] : 0,
                    'max' => is_array($definition) ? $definition['max'] : 100,
                    'items' => $items,
                ];
            })
            ->values()
            ->all();
    }

    /** @return list<array{id: int, name: string, email: string, role: string}> */
    #[Computed]
    public function collaborators(): array
    {
        $journal = $this->journal();

        if (! Gate::allows('manageCollaborators', $journal)) {
            return [];
        }

        return $journal->collaborators()
            ->select(['id', 'forum_journal_id', 'user_id', 'role', 'state'])
            ->where('state', ForumJournalCollaboratorState::Active->value)
            ->with('user:id,name,email')
            ->orderBy('id')
            ->get()
            ->map(static fn (ForumJournalCollaborator $collaborator): array => [
                'id' => $collaborator->id,
                'name' => $collaborator->user->name,
                'email' => $collaborator->user->email,
                'role' => $collaborator->role->label(),
            ])
            ->all();
    }

    /** @return list<array{key: string, label: string, unit: string, unit_label: string, min: float, max: float}> */
    #[Computed]
    public function metricDefinitions(): array
    {
        return $this->metrics->definitions($this->journal()->type);
    }

    /** @return array<string, string> */
    #[Computed]
    public function entryKindOptions(): array
    {
        return collect(ForumJournalEntryKind::cases())
            ->mapWithKeys(static fn (ForumJournalEntryKind $kind): array => [
                $kind->value => $kind->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function collaboratorRoleOptions(): array
    {
        return collect(ForumJournalCollaboratorRole::cases())
            ->mapWithKeys(static fn (ForumJournalCollaboratorRole $role): array => [
                $role->value => $role->label(),
            ])
            ->all();
    }

    public function saveEntry(
        CreateForumJournalEntry $createEntry,
        UpdateForumJournalEntry $updateEntry,
    ): void {
        $actor = $this->requireUser();
        $journal = $this->journal();

        if ($this->editingEntryId === null) {
            $entry = $createEntry->handle($actor, $journal, $this->entryForm->createData());
            $this->feedback = __('forum_journals.feedback.entry_created');
        } else {
            $entry = ForumJournalEntry::query()
                ->where('forum_journal_id', $journal->id)
                ->findOrFail($this->editingEntryId);
            $updateEntry->handle($actor, $journal, $entry, $this->entryForm->updateData());
            $this->feedback = __('forum_journals.feedback.entry_updated');
        }

        $this->journalVersion = $journal->refresh()->lock_version;
        $this->editingEntryId = null;
        $this->entryForm->reset();
        $this->initializeEntryForm();
        unset($this->entries, $this->progressSeries, $this->journalData);
    }

    public function editEntry(int $entryId): void
    {
        $journal = $this->journal();
        Gate::authorize('update', $journal);
        $entry = ForumJournalEntry::query()
            ->with('measurements')
            ->where('forum_journal_id', $journal->id)
            ->findOrFail($entryId);
        $this->editingEntryId = $entry->id;
        $this->entryForm->fillFromEntry($entry);
    }

    public function cancelEntryEdit(): void
    {
        $this->editingEntryId = null;
        $this->entryForm->reset();
        $this->initializeEntryForm();
    }

    public function beginComment(int $entryId): void
    {
        $journal = $this->journal();
        Gate::authorize('comment', $journal);
        $this->entry($journal, $entryId);
        $this->commentEntryId = $entryId;
        $this->commentForm->reset();
        $this->initializeCommentForm();
    }

    public function saveComment(CreateForumJournalComment $createComment): void
    {
        $journal = $this->journal();
        $entry = $this->entry($journal, $this->commentEntryId);
        $data = $this->commentForm->data();
        $createComment->handle(
            $this->requireUser(),
            $journal,
            $entry,
            $data['body'],
            $data['idempotency_key'],
        );
        $this->journalVersion = $journal->refresh()->lock_version;
        $this->commentEntryId = null;
        $this->commentForm->reset();
        $this->initializeCommentForm();
        $this->feedback = __('forum_journals.feedback.comment_created');
        unset($this->entries, $this->journalData);
    }

    public function beginMedia(int $entryId): void
    {
        $journal = $this->journal();
        Gate::authorize('update', $journal);
        $this->entry($journal, $entryId);
        $this->mediaEntryId = $entryId;
        $this->mediaForm->reset();
        $this->initializeMediaForm();
    }

    public function saveMedia(StoreForumJournalMedia $storeMedia): void
    {
        $journal = $this->journal();
        $entry = $this->entry($journal, $this->mediaEntryId);
        $this->mediaForm->validate();
        $upload = $this->mediaForm->upload;
        abort_unless($upload !== null, 422);
        $storeMedia->handle(
            $this->requireUser(),
            $journal,
            $entry,
            $upload,
            $this->mediaForm->altText,
            filled($this->mediaForm->caption) ? $this->mediaForm->caption : null,
            $this->mediaForm->idempotencyKey,
        );
        $this->journalVersion = $journal->refresh()->lock_version;
        $this->mediaEntryId = null;
        $this->mediaForm->reset();
        $this->initializeMediaForm();
        $this->feedback = __('forum_journals.feedback.media_created');
        unset($this->entries, $this->journalData);
    }

    public function grantCollaborator(GrantForumJournalCollaborator $grant): void
    {
        $data = $this->collaboratorForm->data();
        $journal = $this->journal();
        $grant->handle(
            $this->requireUser(),
            $journal,
            $data['email'],
            $data['role'],
        );
        $this->journalVersion = $journal->refresh()->lock_version;
        $this->collaboratorForm->reset();
        $this->feedback = __('forum_journals.feedback.collaborator_granted');
        unset($this->collaborators, $this->journalData);
    }

    public function revokeCollaborator(
        int $collaboratorId,
        RevokeForumJournalCollaborator $revoke,
    ): void {
        $journal = $this->journal();
        $collaborator = ForumJournalCollaborator::query()
            ->where('forum_journal_id', $journal->id)
            ->findOrFail($collaboratorId);
        $revoke->handle($this->requireUser(), $journal, $collaborator);
        $this->journalVersion = $journal->refresh()->lock_version;
        $this->feedback = __('forum_journals.feedback.collaborator_revoked');
        unset($this->collaborators, $this->journalData);
    }

    public function archive(ArchiveForumJournal $archive): void
    {
        $journal = $archive->handle(
            $this->requireUser(),
            $this->journal(),
            $this->journalVersion,
        );
        $this->journalVersion = $journal->lock_version;
        $this->feedback = __('forum_journals.feedback.archived');
        unset($this->journalData);
    }

    public function render()
    {
        return view('livewire.forum.forum-journal-timeline');
    }

    private function initializeEntryForm(): void
    {
        $timezone = Auth::user() instanceof User
            ? Auth::user()->timezone
            : $this->journal()->timezone;
        $this->entryForm->occurredAt = now($timezone)->format('Y-m-d\TH:i');
        $this->entryForm->timezone = $timezone;
        $this->entryForm->idempotencyKey = (string) str()->uuid();
        $this->entryForm->expectedVersion = 0;
    }

    private function initializeCommentForm(): void
    {
        $this->commentForm->idempotencyKey = (string) str()->uuid();
    }

    private function initializeMediaForm(): void
    {
        $this->mediaForm->idempotencyKey = (string) str()->uuid();
    }

    /** @param list<string> $with */
    private function journal(array $with = []): ForumJournal
    {
        return ForumJournal::query()
            ->when($with !== [], fn ($query) => $query->with($with))
            ->findOrFail($this->journalId);
    }

    private function entry(ForumJournal $journal, ?int $entryId): ForumJournalEntry
    {
        abort_unless($entryId !== null, 404);

        return ForumJournalEntry::query()
            ->where('forum_journal_id', $journal->id)
            ->findOrFail($entryId);
    }

    private function requireUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        return $user;
    }

    /** @return array<string, mixed> */
    private function presentEntry(
        ForumJournalEntry $entry,
        ForumJournal $journal,
        LocaleFormatter $formatter,
    ): array {
        return [
            'id' => $entry->id,
            'stable_key' => $entry->stable_key,
            'kind' => $entry->kind->label(),
            'kind_key' => $entry->kind->value,
            'title' => $entry->title,
            'body' => $entry->body,
            'author_name' => $entry->author_name,
            'occurred_at' => $formatter->dateTime($entry->occurred_at, $entry->timezone),
            'version_count' => $entry->versions_count,
            'measurements' => $entry->measurements
                ->sortBy('position')
                ->map(static fn ($measurement): array => [
                    'key' => $measurement->metric_key,
                    'label' => __("forum_journals.metrics.{$measurement->metric_key}"),
                    'value' => $measurement->numeric_value,
                    'unit' => __("forum_journals.units.{$measurement->unit}"),
                ])
                ->values()
                ->all(),
            'comments' => $entry->comments
                ->map(static fn ($comment): array => [
                    'id' => $comment->id,
                    'author_name' => $comment->author_name,
                    'author_initials' => $comment->author_initials,
                    'body' => $comment->body,
                    'created_at' => $formatter->relative($comment->created_at),
                ])
                ->all(),
            'media' => $entry->media
                ->map(static fn ($media): array => [
                    'id' => $media->id,
                    'url' => route('forum.journals.media.show', [
                        'forumJournal' => $journal,
                        'forumJournalMedia' => $media,
                    ]),
                    'alt_text' => $media->alt_text,
                    'caption' => $media->caption,
                ])
                ->all(),
        ];
    }
}
