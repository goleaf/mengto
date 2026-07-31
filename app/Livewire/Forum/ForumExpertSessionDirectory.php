<?php

declare(strict_types=1);

namespace App\Livewire\Forum;

use App\Actions\CreateForumExpertSession;
use App\Enums\ForumExpertQuestionModerationStatus;
use App\Enums\ForumExpertQuestionStatus;
use App\Livewire\Forms\ForumExpertSessionForm;
use App\Models\ExpertProfile;
use App\Models\ForumExpertSession;
use App\Models\User;
use App\Services\ForumExpertSessionHostEligibility;
use App\Services\LocaleFormatter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class ForumExpertSessionDirectory extends Component
{
    use WithPagination;

    public ForumExpertSessionForm $form;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 'all')]
    public string $scope = 'all';

    #[Url(except: 'upcoming')]
    public string $period = 'upcoming';

    public string $feedback = '';

    protected ForumExpertSessionHostEligibility $eligibility;

    protected LocaleFormatter $formatter;

    public function boot(
        ForumExpertSessionHostEligibility $eligibility,
        LocaleFormatter $formatter,
    ): void {
        $this->eligibility = $eligibility;
        $this->formatter = $formatter;
    }

    public function mount(): void
    {
        Gate::authorize('viewAny', ForumExpertSession::class);
        $this->initializeForm();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedScope(): void
    {
        $this->resetPage();
    }

    public function updatedPeriod(): void
    {
        $this->resetPage();
    }

    public function updatedFormExpertProfileId(): void
    {
        $profile = $this->candidateProfile($this->form->expertProfileId);

        if ($profile === null) {
            return;
        }

        $this->form->professionalScope = $profile->primary_type;
        $this->form->jurisdiction = $this->eligibility->defaultJurisdiction($profile);
    }

    /** @return LengthAwarePaginator<int, array<string, mixed>> */
    #[Computed]
    public function sessions(): LengthAwarePaginator
    {
        $filters = $this->validatedFilters();
        $query = ForumExpertSession::query()
            ->publiclyVisible()
            ->select([
                'id',
                'expert_profile_id',
                'stable_key',
                'host_name_snapshot',
                'professional_scope',
                'jurisdiction',
                'title',
                'summary',
                'locale',
                'timezone',
                'status',
                'question_opens_at',
                'question_closes_at',
                'starts_at',
                'ends_at',
                'archived_at',
            ])
            ->with('expertProfile:id,slug,public_name')
            ->withCount([
                'questions as approved_question_count' => static fn (Builder $questions) => $questions
                    ->where('moderation_status', ForumExpertQuestionModerationStatus::Approved->value)
                    ->whereNotIn('status', [
                        ForumExpertQuestionStatus::Withdrawn->value,
                        ForumExpertQuestionStatus::Removed->value,
                    ]),
                'answers as published_answer_count',
            ]);

        if ($filters['search'] !== '') {
            $like = '%'.$filters['search'].'%';
            $query->where(function (Builder $search) use ($like): void {
                $search
                    ->where('title', 'like', $like)
                    ->orWhere('summary', 'like', $like)
                    ->orWhere('host_name_snapshot', 'like', $like)
                    ->orWhere('professional_scope', 'like', $like)
                    ->orWhere('jurisdiction', 'like', $like);
            });
        }

        if ($filters['scope'] !== 'all') {
            $query->where('professional_scope', $filters['scope']);
        }

        match ($filters['period']) {
            'past' => $query->where('ends_at', '<', now())->orderByDesc('starts_at'),
            'all' => $query->orderBy('starts_at'),
            default => $query->where('ends_at', '>=', now())->orderBy('starts_at'),
        };

        $paginator = $query
            ->orderBy('id')
            ->paginate(12);

        return $paginator->through($this->presentSession(...));
    }

    /** @return array<int, string> */
    #[Computed]
    public function profileOptions(): array
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return [];
        }

        return $this->eligibility->candidateProfiles($user)
            ->filter(fn (ExpertProfile $profile): bool => $this->eligibility->allows(
                $user,
                $profile,
                $profile->primary_type,
                $this->eligibility->defaultJurisdiction($profile),
            ))
            ->mapWithKeys(fn (ExpertProfile $profile): array => [
                $profile->id => $profile->public_name.' · '.$this->scopeLabel($profile->primary_type),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function scopeOptions(): array
    {
        return ForumExpertSession::query()
            ->publiclyVisible()
            ->select(['professional_scope'])
            ->distinct()
            ->orderBy('professional_scope')
            ->limit(100)
            ->pluck('professional_scope')
            ->mapWithKeys(fn (string $scope): array => [$scope => $this->scopeLabel($scope)])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function localeOptions(): array
    {
        return collect(config('platform.supported_locales', ['en']))
            ->mapWithKeys(static fn (string $locale): array => [
                $locale => __('forum_expert_sessions.locales.'.$locale),
            ])
            ->all();
    }

    #[Computed]
    public function canCreate(): bool
    {
        return Gate::allows('create', ForumExpertSession::class);
    }

    public function create(CreateForumExpertSession $createSession): void
    {
        $session = $createSession->handle($this->requireUser(), $this->form->data());
        $this->feedback = __('forum_expert_sessions.feedback.created');
        $this->form->reset();
        $this->initializeForm();
        $this->redirectRoute('forum.expert-sessions.show', $session, navigate: true);
    }

    public function render()
    {
        return view('livewire.forum.forum-expert-session-directory');
    }

    /** @return array{search: string, scope: string, period: string} */
    private function validatedFilters(): array
    {
        $scopeKeys = array_keys($this->scopeOptions());
        $validator = validator([
            'search' => trim($this->search),
            'scope' => $this->scope,
            'period' => $this->period,
        ], [
            'search' => ['nullable', 'string', 'max:120'],
            'scope' => ['required', Rule::in(['all', ...$scopeKeys])],
            'period' => ['required', Rule::in(['upcoming', 'past', 'all'])],
        ]);

        if ($validator->fails()) {
            $this->search = '';
            $this->scope = 'all';
            $this->period = 'upcoming';

            return ['search' => '', 'scope' => 'all', 'period' => 'upcoming'];
        }

        /** @var array{search: string|null, scope: string, period: string} $validated */
        $validated = $validator->validated();

        return [
            'search' => (string) ($validated['search'] ?? ''),
            'scope' => $validated['scope'],
            'period' => $validated['period'],
        ];
    }

    private function initializeForm(): void
    {
        $user = Auth::user();
        $timezone = $user instanceof User ? $user->timezone : 'UTC';
        $locale = $user instanceof User ? $user->locale : app()->getLocale();
        $questionOpensAt = now($timezone)->addDay()->startOfHour();
        $startsAt = $questionOpensAt->clone()->addDays(2);
        $profiles = $this->profileOptions();

        $this->form->expertProfileId = array_key_first($profiles);
        $this->form->timezone = $timezone;
        $this->form->locale = $locale;
        $this->form->questionOpensAt = $questionOpensAt->format('Y-m-d\TH:i');
        $this->form->questionClosesAt = $startsAt->format('Y-m-d\TH:i');
        $this->form->startsAt = $startsAt->format('Y-m-d\TH:i');
        $this->form->endsAt = $startsAt->clone()->addHours(2)->format('Y-m-d\TH:i');
        $this->form->idempotencyKey = (string) str()->uuid();
        $this->updatedFormExpertProfileId();
    }

    private function candidateProfile(?int $profileId): ?ExpertProfile
    {
        $user = Auth::user();

        if (! $user instanceof User || $profileId === null) {
            return null;
        }

        return $this->eligibility->candidateProfiles($user)->firstWhere('id', $profileId);
    }

    private function scopeLabel(string $scope): string
    {
        $key = 'forum_expert_sessions.scopes.'.$scope;

        return Lang::has($key) ? __($key) : str($scope)->replace('-', ' ')->headline()->toString();
    }

    /** @return array<string, mixed> */
    private function presentSession(ForumExpertSession $session): array
    {
        return [
            'id' => $session->id,
            'stable_key' => $session->stable_key,
            'title' => $session->title,
            'summary' => $session->summary,
            'host_name' => $session->host_name_snapshot,
            'host_profile_url' => route('experts.show', $session->expertProfile),
            'professional_scope' => $this->scopeLabel($session->professional_scope),
            'jurisdiction' => $session->jurisdiction,
            'phase' => __('forum_expert_sessions.phases.'.$session->phase()),
            'starts_at' => $this->formatter->dateTime(
                $session->starts_at,
                $session->timezone,
            ),
            'timezone' => $session->timezone,
            'questions' => (int) $session->getAttribute('approved_question_count'),
            'answers' => (int) $session->getAttribute('published_answer_count'),
            'url' => route('forum.expert-sessions.show', $session),
        ];
    }

    private function requireUser(): User
    {
        $user = Auth::user();

        abort_unless($user instanceof User, 401);

        return $user;
    }
}
