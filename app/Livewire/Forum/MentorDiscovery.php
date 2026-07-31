<?php

declare(strict_types=1);

namespace App\Livewire\Forum;

use App\Actions\RequestMentorship;
use App\Enums\ForumMentorshipType;
use App\Livewire\Forms\MentorshipRequestForm;
use App\Models\ForumMentorScope;
use App\Models\User;
use App\Services\ForumCategoryTree;
use App\Services\MentorMatcher;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class MentorDiscovery extends Component
{
    public MentorshipRequestForm $form;

    public string $type = 'first-time-owner';

    public string $language = 'en';

    public string $locationScope = '';

    public string $communicationPreference = 'platform';

    public ?int $forumCategoryId = null;

    /** @var list<int> */
    public array $taxonIds = [];

    #[Locked]
    public string $idempotencyKey;

    public string $feedback = '';

    private AuthFactory $auth;

    private ForumCategoryTree $categoryTree;

    private MentorMatcher $matcher;

    private RequestMentorship $requestAction;

    public function boot(
        AuthFactory $auth,
        ForumCategoryTree $categoryTree,
        MentorMatcher $matcher,
        RequestMentorship $requestAction,
    ): void {
        $this->auth = $auth;
        $this->categoryTree = $categoryTree;
        $this->matcher = $matcher;
        $this->requestAction = $requestAction;
    }

    public function mount(): void
    {
        $user = $this->requireUser();
        $this->language = $user->locale;
        $this->locationScope = '';
        $this->form->language = $user->locale;
        $this->idempotencyKey = (string) str()->uuid();
    }

    /** @return array<string, string> */
    #[Computed]
    public function typeOptions(): array
    {
        return collect(ForumMentorshipType::cases())
            ->mapWithKeys(static fn (ForumMentorshipType $type): array => [
                $type->value => $type->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function localeOptions(): array
    {
        return collect(config('platform.supported_locales', ['en']))
            ->mapWithKeys(static fn (string $locale): array => [
                $locale => __("forum_mentorship.languages.{$locale}"),
            ])
            ->all();
    }

    /** @return array<int, string> */
    #[Computed]
    public function categoryOptions(): array
    {
        return $this->categoryTree->rootOptions(app()->getLocale());
    }

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function matches(): array
    {
        $validator = validator([
            'type' => $this->type,
            'language' => $this->language,
            'location_scope' => $this->locationScope,
            'communication_preference' => $this->communicationPreference,
            'forum_category_id' => $this->forumCategoryId,
            'taxon_ids' => $this->taxonIds,
        ], [
            'type' => ['required', Rule::enum(ForumMentorshipType::class)],
            'language' => [
                'required',
                Rule::in(config('platform.supported_locales', ['en'])),
            ],
            'location_scope' => ['nullable', 'string', 'max:160'],
            'communication_preference' => ['required', Rule::in(['platform'])],
            'forum_category_id' => ['nullable', 'integer', 'exists:forum_categories,id'],
            'taxon_ids' => ['array', 'max:1'],
            'taxon_ids.*' => ['integer', 'exists:taxa,id'],
        ]);

        if ($validator->fails()) {
            return [];
        }

        $validated = $validator->validated();

        return $this->matcher->find(
            requester: $this->requireUser(),
            type: ForumMentorshipType::from((string) $validated['type']),
            language: (string) $validated['language'],
            communicationPreference: (string) $validated['communication_preference'],
            forumCategoryId: isset($validated['forum_category_id'])
                ? (int) $validated['forum_category_id']
                : null,
            taxonId: isset($validated['taxon_ids'][0])
                ? (int) $validated['taxon_ids'][0]
                : null,
            locationScope: filled($validated['location_scope'] ?? null)
                ? trim((string) $validated['location_scope'])
                : null,
        )->map(static fn ($match): array => [
            'scope_id' => $match->scopeId,
            'mentor_name' => $match->mentorName,
            'headline' => $match->headline,
            'summary' => $match->summary,
            'type' => $match->type->label(),
            'languages' => $match->languages,
            'languages_label' => implode(', ', array_map(
                static fn (string $locale): string => __(
                    "forum_mentorship.languages.{$locale}",
                ),
                $match->languages,
            )),
            'location_scope' => $match->locationScope,
            'category' => $match->categoryName,
            'taxon' => $match->scientificName,
            'professionally_verified' => $match->professionallyVerified,
            'score' => $match->score,
            'reasons' => collect($match->reasonTranslationKeys)
                ->map(static fn (string $key): string => __($key))
                ->all(),
        ])->all();
    }

    public function refreshMatches(): void
    {
        $this->validate([
            'type' => ['required', Rule::enum(ForumMentorshipType::class)],
            'language' => [
                'required',
                Rule::in(config('platform.supported_locales', ['en'])),
            ],
            'locationScope' => ['nullable', 'string', 'max:160'],
            'communicationPreference' => ['required', Rule::in(['platform'])],
            'forumCategoryId' => ['nullable', 'integer', 'exists:forum_categories,id'],
            'taxonIds' => ['array', 'max:1'],
            'taxonIds.*' => ['integer', 'exists:taxa,id'],
        ]);
        $this->form->language = $this->language;
        $this->form->locationScope = $this->locationScope;
        unset($this->matches);
    }

    public function request(int $scopeId): void
    {
        $scope = ForumMentorScope::query()->findOrFail($scopeId);
        $this->requestAction->handle(
            $this->requireUser(),
            $scope,
            $this->form->data($this->idempotencyKey),
        );
        $this->form->reset();
        $this->form->language = $this->language;
        $this->form->locationScope = $this->locationScope;
        $this->idempotencyKey = (string) str()->uuid();
        $this->feedback = __('forum_mentorship.feedback.request_sent');
        unset($this->matches);
        $this->dispatch('mentorship-updated');
    }

    public function render(): View
    {
        return view('livewire.forum.mentor-discovery');
    }

    private function requireUser(): User
    {
        $user = $this->auth->user();

        abort_unless($user instanceof User && $user->isActive(), 403);

        return $user;
    }
}
