<?php

declare(strict_types=1);

namespace App\Livewire\Forum;

use App\Actions\ReviewProfessionalCredential;
use App\Enums\CredentialStatus;
use App\Enums\KnowledgeCorrectionStatus;
use App\Models\Credential;
use App\Models\ForumCategory;
use App\Models\ForumCategoryTranslation;
use App\Models\KnowledgeArticle;
use App\Models\TaxonImport;
use App\Models\User;
use App\Services\LocaleFormatter;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

final class AdminDashboard extends Component
{
    private CacheRepository $cache;

    private LocaleFormatter $formatter;

    private ReviewProfessionalCredential $reviewCredentialAction;

    #[Url(as: 'view')]
    public string $tab = 'categories';

    #[Url(as: 'q')]
    public string $search = '';

    #[Locked]
    public ?int $selectedCategoryId = null;

    public string $translationName = '';

    public string $visibility = 'public';

    public string $moderationLevel = 'standard';

    #[Locked]
    public ?int $selectedCredentialId = null;

    #[Locked]
    public string $verificationIdempotencyKey = '';

    public string $credentialTargetStatus = 'in-review';

    public string $verificationInternalReason = '';

    public function boot(
        CacheRepository $cache,
        LocaleFormatter $formatter,
        ReviewProfessionalCredential $reviewCredentialAction,
    ): void {
        $this->cache = $cache;
        $this->formatter = $formatter;
        $this->reviewCredentialAction = $reviewCredentialAction;
    }

    public function mount(): void
    {
        $this->administrator();

        if (! in_array($this->tab, [
            'categories',
            'guides',
            'taxonomy',
            'verification',
            'moderation',
        ], true)) {
            $this->tab = 'categories';
        }
    }

    /** @return list<array<string, int|string|bool|null>> */
    #[Computed]
    public function categories(): array
    {
        $locale = App::currentLocale();
        $search = trim($this->search);

        return ForumCategory::query()
            ->select([
                'id',
                'parent_id',
                'stable_key',
                'slug',
                'position',
                'visibility',
                'moderation_level',
                'is_system_managed',
                'is_active',
            ])
            ->when(
                filled($search),
                fn ($query) => $query->where(static function ($searchQuery) use ($search): void {
                    $searchQuery
                        ->where('stable_key', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%');
                }),
            )
            ->with([
                'translations' => fn ($query) => $query
                    ->select(['id', 'forum_category_id', 'locale', 'name'])
                    ->where('locale', $locale),
            ])
            ->ordered()
            ->limit(100)
            ->get()
            ->map(static function (ForumCategory $category): array {
                $translation = $category->translations->first();

                return [
                    'id' => $category->id,
                    'stable_key' => $category->stable_key,
                    'slug' => $category->slug,
                    'name' => $translation instanceof ForumCategoryTranslation
                        ? $translation->name
                        : $category->slug,
                    'visibility' => $category->visibility,
                    'moderation_level' => $category->moderation_level,
                    'is_system_managed' => $category->is_system_managed,
                    'is_active' => $category->is_active,
                ];
            })
            ->all();
    }

    /** @return list<array<string, int|string|null>> */
    #[Computed]
    public function imports(): array
    {
        return TaxonImport::query()
            ->select([
                'id',
                'taxon_source_id',
                'source_version',
                'state',
                'processed_rows',
                'error_rows',
                'warning_rows',
                'created_at',
            ])
            ->with('source:id,name,stable_key')
            ->latest('id')
            ->limit(20)
            ->get()
            ->map(static fn (TaxonImport $import): array => [
                'id' => $import->id,
                'source' => $import->source->name,
                'version' => $import->source_version,
                'state' => $import->state->value,
                'processed' => $import->processed_rows,
                'errors' => $import->error_rows,
                'warnings' => $import->warning_rows,
            ])
            ->all();
    }

    /** @return list<array<string, int|string|null>> */
    #[Computed]
    public function guides(): array
    {
        $search = trim($this->search);

        return KnowledgeArticle::query()
            ->select([
                'id',
                'slug',
                'title',
                'status',
                'language',
                'current_version',
                'updated_at',
            ])
            ->withCount([
                'activeCollaborators',
                'corrections as pending_corrections_count' => fn ($query) => $query
                    ->whereIn('status', [
                        KnowledgeCorrectionStatus::Submitted->value,
                        KnowledgeCorrectionStatus::Accepted->value,
                    ]),
            ])
            ->when(
                filled($search),
                fn ($query) => $query->where(static function ($searchQuery) use ($search): void {
                    $searchQuery
                        ->where('title', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%');
                }),
            )
            ->latest('updated_at')
            ->latest('id')
            ->limit(100)
            ->get()
            ->map(fn (KnowledgeArticle $article): array => [
                'id' => $article->id,
                'title' => $article->title,
                'status' => $article->status->value,
                'status_label' => $article->status->label(),
                'language' => $article->language,
                'version' => $article->current_version,
                'collaborators' => (int) $article->getAttribute('active_collaborators_count'),
                'corrections' => (int) $article->getAttribute('pending_corrections_count'),
                'updated' => $this->formatter->date($article->updated_at),
                'edit_url' => route('knowledge.guides.edit', $article),
                'public_url' => $article->status->isPublic()
                    ? route('knowledge.articles.show', $article)
                    : null,
            ])
            ->all();
    }

    /** @return list<array<string, int|string|null>> */
    #[Computed]
    public function credentials(): array
    {
        return Credential::query()
            ->select([
                'id',
                'expert_profile_id',
                'type',
                'title',
                'jurisdiction',
                'status',
                'expires_at',
                'renewal_due_at',
                'public_summary_translation_key',
            ])
            ->with('expertProfile:id,public_name')
            ->whereIn('status', [
                CredentialStatus::Submitted->value,
                CredentialStatus::InReview->value,
                CredentialStatus::Expiring->value,
                CredentialStatus::Suspended->value,
            ])
            ->oldest('expires_at')
            ->oldest('id')
            ->limit(50)
            ->get()
            ->map(fn (Credential $credential): array => [
                'id' => $credential->id,
                'professional' => $credential->expertProfile->public_name
                    ?? __('forum_admin.verification.unknown_professional'),
                'title' => $credential->title,
                'type' => $credential->type,
                'jurisdiction' => $credential->jurisdiction
                    ?? __('forum_admin.verification.no_jurisdiction'),
                'status' => $credential->effectiveStatus()->label(),
                'expires' => $this->formatter->date($credential->expires_at),
                'summary' => $credential->public_summary_translation_key !== null
                    && trans()->has($credential->public_summary_translation_key)
                        ? __($credential->public_summary_translation_key)
                        : null,
            ])
            ->all();
    }

    public function selectCategory(int $categoryId): void
    {
        $category = $this->authorizedCategory($categoryId);
        $translation = $category->translations()
            ->where('locale', App::currentLocale())
            ->first();
        $this->selectedCategoryId = $category->id;
        $this->translationName = $translation instanceof ForumCategoryTranslation
            ? $translation->name
            : '';
        $this->visibility = $category->visibility;
        $this->moderationLevel = $category->moderation_level;
    }

    public function saveCategory(): void
    {
        $category = $this->authorizedCategory((int) $this->selectedCategoryId);
        $validated = $this->validate([
            'translationName' => ['required', 'string', 'max:180'],
            'visibility' => ['required', 'in:public,members,restricted,hidden'],
            'moderationLevel' => ['required', 'in:standard,review,high-risk,emergency'],
        ]);
        $category->forceFill([
            'visibility' => $validated['visibility'],
            'moderation_level' => $validated['moderationLevel'],
        ])->save();
        ForumCategoryTranslation::query()->updateOrCreate(
            [
                'forum_category_id' => $category->id,
                'locale' => App::currentLocale(),
            ],
            [
                'name' => $validated['translationName'],
                'is_reviewed' => true,
            ],
        );
        $this->invalidateCategoryCaches();
        unset($this->categories);
        session()->flash('feedback', __('forum_admin.feedback.category_saved'));
    }

    public function selectCredential(int $credentialId): void
    {
        $this->administrator();
        Credential::query()->select(['id'])->findOrFail($credentialId);
        $this->selectedCredentialId = $credentialId;
        $this->credentialTargetStatus = CredentialStatus::InReview->value;
        $this->verificationInternalReason = '';
        $this->verificationIdempotencyKey = (string) Str::uuid();
    }

    public function reviewCredential(): void
    {
        $administrator = $this->administrator();
        $validated = $this->validate([
            'selectedCredentialId' => ['required', 'integer', 'exists:credentials,id'],
            'credentialTargetStatus' => [
                'required',
                'in:in-review,verified,rejected,suspended,revoked',
            ],
            'verificationInternalReason' => ['required', 'string', 'min:20', 'max:2000'],
            'verificationIdempotencyKey' => ['required', 'uuid'],
        ]);
        $targetStatus = CredentialStatus::from($validated['credentialTargetStatus']);
        $reasonKey = match ($targetStatus) {
            CredentialStatus::Verified => 'credential_verification.reason.approved',
            CredentialStatus::Suspended => 'credential_verification.reason.suspended',
            CredentialStatus::Revoked => 'credential_verification.reason.revoked',
            default => 'credential_verification.reason.information-required',
        };

        $this->reviewCredentialAction->handle(
            reviewer: $administrator,
            credentialId: (int) $validated['selectedCredentialId'],
            targetStatus: $targetStatus,
            reasonTranslationKey: $reasonKey,
            internalReason: $validated['verificationInternalReason'],
            idempotencyKey: $validated['verificationIdempotencyKey'],
        );
        $this->selectedCredentialId = null;
        $this->verificationInternalReason = '';
        $this->verificationIdempotencyKey = '';
        unset($this->credentials);
        session()->flash('feedback', __('forum_admin.feedback.credential_reviewed'));
    }

    public function invalidateCategoryCaches(): void
    {
        $this->administrator();
        $locales = config('platform.supported_locales', ['en']);

        foreach ($locales as $locale) {
            $this->cache->forget("forum:category-tree:v1:locale:{$locale}");
        }

        session()->flash('feedback', __('forum_admin.feedback.cache_invalidated'));
    }

    private function authorizedCategory(int $categoryId): ForumCategory
    {
        $administrator = $this->administrator();
        $category = ForumCategory::query()->findOrFail($categoryId);

        abort_unless($administrator->can('manage', $category), 403);

        return $category;
    }

    private function administrator(): User
    {
        $user = request()->user();

        abort_unless($user instanceof User && $user->isAdministrator(), 403);

        return $user;
    }

    public function render(): View
    {
        return view('livewire.forum.admin-dashboard');
    }
}
