<?php

declare(strict_types=1);

namespace App\Livewire\Forum;

use App\Actions\CreateForumJournal;
use App\Enums\ForumJournalStatus;
use App\Enums\ForumJournalType;
use App\Enums\ForumVisibility;
use App\Livewire\Forms\ForumJournalForm;
use App\Models\ForumJournal;
use App\Models\User;
use App\Services\ForumTaxonomy;
use App\Services\LocaleFormatter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class ForumJournalDirectory extends Component
{
    use WithPagination;

    protected LocaleFormatter $formatter;

    protected ForumTaxonomy $taxonomy;

    public ForumJournalForm $form;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 'all')]
    public string $type = 'all';

    #[Url(except: 'active')]
    public string $status = 'active';

    public string $feedback = '';

    public function boot(
        LocaleFormatter $formatter,
        ForumTaxonomy $taxonomy,
    ): void {
        $this->formatter = $formatter;
        $this->taxonomy = $taxonomy;
    }

    public function mount(): void
    {
        Gate::authorize('viewAny', ForumJournal::class);
        $user = $this->requireUser();
        $this->form->startedOn = now($user->timezone)->toDateString();
        $this->form->timezone = $user->timezone;
        $this->form->locale = $user->locale;
        $this->form->idempotencyKey = (string) str()->uuid();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    /** @return LengthAwarePaginator<int, array<string, mixed>> */
    #[Computed]
    public function journals(): LengthAwarePaginator
    {
        $filters = $this->validatedFilters();
        $user = $this->requireUser();
        $query = ForumJournal::query()
            ->forUserDirectory($user)
            ->with([
                'topic:id,title,slug,body,visibility,language,last_activity_at',
            ])
            ->withCount('entries');

        if ($filters['search'] !== '') {
            $query->whereHas('topic', function (Builder $topics) use ($filters): void {
                $topics
                    ->where('title', 'like', '%'.$filters['search'].'%')
                    ->orWhere('body', 'like', '%'.$filters['search'].'%');
            });
        }

        if ($filters['type'] !== 'all') {
            $query->where('type', $filters['type']);
        }

        if ($filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        return $query
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->through(fn (ForumJournal $journal): array => $this->presentJournal(
                $journal,
                $this->formatter,
            ));
    }

    /** @return array<string, string> */
    #[Computed]
    public function categoryOptions(): array
    {
        return $this->taxonomy->categoryOptions();
    }

    /** @return array<string, string> */
    #[Computed]
    public function typeOptions(): array
    {
        return collect(ForumJournalType::cases())
            ->mapWithKeys(static fn (ForumJournalType $type): array => [
                $type->value => $type->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function visibilityOptions(): array
    {
        return collect([
            ForumVisibility::Public,
            ForumVisibility::Members,
            ForumVisibility::Experts,
            ForumVisibility::Link,
            ForumVisibility::Private,
        ])->mapWithKeys(static fn (ForumVisibility $visibility): array => [
            $visibility->value => $visibility->label(),
        ])->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function localeOptions(): array
    {
        return collect(config('platform.supported_locales', ['en']))
            ->mapWithKeys(static fn (string $locale): array => [
                $locale => __('forum_journals.locales.'.$locale),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function statusOptions(): array
    {
        return [
            'all' => __('forum_journals.filters.all_statuses'),
            ForumJournalStatus::Active->value => ForumJournalStatus::Active->label(),
            ForumJournalStatus::Archived->value => ForumJournalStatus::Archived->label(),
        ];
    }

    public function create(CreateForumJournal $createJournal): void
    {
        $journal = $createJournal->handle($this->requireUser(), $this->form->data());
        $this->feedback = __('forum_journals.feedback.created');
        $this->form->reset();
        $user = $this->requireUser();
        $this->form->startedOn = now($user->timezone)->toDateString();
        $this->form->timezone = $user->timezone;
        $this->form->locale = $user->locale;
        $this->form->idempotencyKey = (string) str()->uuid();
        $this->redirectRoute('forum.topics.show', $journal->topic, navigate: true);
    }

    public function render()
    {
        return view('livewire.forum.forum-journal-directory');
    }

    /** @return array{search: string, type: string, status: string} */
    private function validatedFilters(): array
    {
        $validator = validator([
            'search' => trim($this->search),
            'type' => $this->type,
            'status' => $this->status,
        ], [
            'search' => ['nullable', 'string', 'max:120'],
            'type' => [
                'required',
                Rule::in([
                    'all',
                    ...array_map(
                        static fn (ForumJournalType $type): string => $type->value,
                        ForumJournalType::cases(),
                    ),
                ]),
            ],
            'status' => [
                'required',
                Rule::in([
                    'all',
                    ForumJournalStatus::Active->value,
                    ForumJournalStatus::Archived->value,
                ]),
            ],
        ]);

        if ($validator->fails()) {
            $this->search = '';
            $this->type = 'all';
            $this->status = ForumJournalStatus::Active->value;

            return [
                'search' => '',
                'type' => 'all',
                'status' => ForumJournalStatus::Active->value,
            ];
        }

        /** @var array{search: string|null, type: string, status: string} $validated */
        $validated = $validator->validated();

        return [
            'search' => (string) ($validated['search'] ?? ''),
            'type' => $validated['type'],
            'status' => $validated['status'],
        ];
    }

    private function requireUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        return $user;
    }

    /** @return array<string, mixed> */
    private function presentJournal(
        ForumJournal $journal,
        LocaleFormatter $formatter,
    ): array {
        return [
            'id' => $journal->id,
            'stable_key' => $journal->stable_key,
            'title' => $journal->topic->title,
            'summary' => str($journal->topic->body)->squish()->limit(180)->toString(),
            'type' => $journal->type->label(),
            'status' => $journal->status->label(),
            'visibility' => $journal->topic->visibility->label(),
            'started_on' => $formatter->date($journal->started_on),
            'updated_at' => $formatter->relative($journal->updated_at),
            'entry_count' => $journal->entries_count,
            'url' => route('forum.topics.show', $journal->topic),
            'export_url' => route('forum.journals.export', $journal),
        ];
    }
}
