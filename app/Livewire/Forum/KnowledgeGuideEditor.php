<?php

declare(strict_types=1);

namespace App\Livewire\Forum;

use App\Actions\CreateKnowledgeGuide;
use App\Actions\ManageKnowledgeCollaborator;
use App\Actions\ReviewKnowledgeCorrection;
use App\Actions\RollbackKnowledgeGuideVersion;
use App\Actions\SaveKnowledgeGuideRevision;
use App\Actions\SetKnowledgeEditorialLock;
use App\Actions\TransitionKnowledgeGuide;
use App\Enums\KnowledgeCollaboratorRole;
use App\Enums\KnowledgeCorrectionStatus;
use App\Enums\KnowledgeStatus;
use App\Livewire\Forms\KnowledgeGuideForm;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeArticleCollaborator;
use App\Models\KnowledgeCorrection;
use App\Models\KnowledgeVersion;
use App\Models\User;
use App\Policies\KnowledgeArticlePolicy;
use App\Services\ForumTaxonomy;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class KnowledgeGuideEditor extends Component
{
    public KnowledgeGuideForm $form;

    #[Locked]
    public ?int $articleId = null;

    #[Locked]
    public int $articleLockVersion = 0;

    public string $workflowStatus = '';

    public string $workflowReason = '';

    public ?int $replacementArticleId = null;

    public ?int $rollbackVersionId = null;

    public string $rollbackReason = '';

    public string $collaboratorEmail = '';

    public string $collaboratorRole = 'contributor';

    public ?int $selectedCorrectionId = null;

    public string $correctionDecision = 'accepted';

    public string $correctionReason = '';

    public string $editorialLockReason = '';

    public string $feedback = '';

    private AuthFactory $auth;

    private CreateKnowledgeGuide $createAction;

    private Gate $gate;

    private ManageKnowledgeCollaborator $manageCollaboratorAction;

    private KnowledgeArticlePolicy $policy;

    private ReviewKnowledgeCorrection $reviewCorrectionAction;

    private RollbackKnowledgeGuideVersion $rollbackAction;

    private SaveKnowledgeGuideRevision $saveAction;

    private SetKnowledgeEditorialLock $setLockAction;

    private ForumTaxonomy $taxonomy;

    private TransitionKnowledgeGuide $transitionAction;

    public function boot(
        AuthFactory $auth,
        Gate $gate,
        KnowledgeArticlePolicy $policy,
        ForumTaxonomy $taxonomy,
        CreateKnowledgeGuide $createAction,
        SaveKnowledgeGuideRevision $saveAction,
        TransitionKnowledgeGuide $transitionAction,
        ManageKnowledgeCollaborator $manageCollaboratorAction,
        ReviewKnowledgeCorrection $reviewCorrectionAction,
        RollbackKnowledgeGuideVersion $rollbackAction,
        SetKnowledgeEditorialLock $setLockAction,
    ): void {
        $this->auth = $auth;
        $this->gate = $gate;
        $this->policy = $policy;
        $this->taxonomy = $taxonomy;
        $this->createAction = $createAction;
        $this->saveAction = $saveAction;
        $this->transitionAction = $transitionAction;
        $this->manageCollaboratorAction = $manageCollaboratorAction;
        $this->reviewCorrectionAction = $reviewCorrectionAction;
        $this->rollbackAction = $rollbackAction;
        $this->setLockAction = $setLockAction;
    }

    public function mount(?int $articleId = null): void
    {
        $this->articleId = $articleId;

        if ($articleId === null) {
            $this->gate->forUser($this->user())->authorize('create', KnowledgeArticle::class);
            $this->form->language = $this->user()->locale;
            $this->form->category = (string) array_key_first($this->taxonomy->categoryOptions());
            $this->form->changeSummary = __('knowledge.defaults.initial_change_summary');

            return;
        }

        $article = $this->article();
        $this->gate->forUser($this->user())->authorize('update', $article);
        $this->articleLockVersion = $article->lock_version;
        $this->form->fillFromArticle($article);
        $this->workflowStatus = $article->status->allowedTransitions()[0]->value ?? '';
    }

    /** @return array<string, int|string|bool|null> */
    #[Computed]
    public function articleData(): array
    {
        if ($this->articleId === null) {
            return [
                'id' => 0,
                'slug' => null,
                'status' => KnowledgeStatus::Draft->value,
                'status_label' => KnowledgeStatus::Draft->label(),
                'version' => 0,
                'is_locked' => false,
                'lock_reason' => null,
            ];
        }

        $article = $this->article();

        return [
            'id' => $article->id,
            'slug' => $article->slug,
            'status' => $article->status->value,
            'status_label' => $article->status->label(),
            'version' => $article->current_version,
            'is_locked' => $article->editorial_locked_at !== null,
            'lock_reason' => $article->editorial_lock_reason,
        ];
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
        return collect(['guide', 'checklist', 'faq', 'comparison', 'local-guide'])
            ->mapWithKeys(static fn (string $type): array => [
                $type => __("knowledge.types.{$type}"),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function difficultyOptions(): array
    {
        return collect(['beginner', 'intermediate', 'advanced'])
            ->mapWithKeys(static fn (string $difficulty): array => [
                $difficulty => __("knowledge.difficulty.{$difficulty}"),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function localeOptions(): array
    {
        return collect(config('platform.supported_locales', ['en']))
            ->mapWithKeys(static fn (string $locale): array => [
                $locale => __("auth.locales.{$locale}"),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function collaboratorRoleOptions(): array
    {
        return collect(KnowledgeCollaboratorRole::cases())
            ->mapWithKeys(static fn (KnowledgeCollaboratorRole $role): array => [
                $role->value => $role->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function workflowOptions(): array
    {
        if ($this->articleId === null) {
            return [];
        }

        $article = $this->article();
        $user = $this->user();

        return collect($article->status->allowedTransitions())
            ->filter(fn (KnowledgeStatus $target): bool => $this->canTransition(
                $user,
                $article,
                $target,
            ))
            ->mapWithKeys(static fn (KnowledgeStatus $status): array => [
                $status->value => $status->label(),
            ])
            ->all();
    }

    /** @return list<array<string, int|string|null>> */
    #[Computed]
    public function versions(): array
    {
        if ($this->articleId === null) {
            return [];
        }

        return KnowledgeVersion::query()
            ->select([
                'id',
                'article_id',
                'version_number',
                'edited_by',
                'change_summary',
                'created_at',
            ])
            ->where('article_id', $this->articleId)
            ->latest('version_number')
            ->limit(30)
            ->get()
            ->map(static fn (KnowledgeVersion $version): array => [
                'id' => $version->id,
                'number' => $version->version_number,
                'editor' => $version->edited_by,
                'summary' => $version->change_summary,
                'created' => $version->created_at?->toIso8601String(),
            ])
            ->all();
    }

    /** @return list<array<string, int|string|null>> */
    #[Computed]
    public function collaborators(): array
    {
        if ($this->articleId === null) {
            return [];
        }

        return KnowledgeArticleCollaborator::query()
            ->select([
                'id',
                'article_id',
                'user_id',
                'role',
                'attribution_name',
                'revoked_at',
                'created_at',
            ])
            ->with('user:id,name,email')
            ->where('article_id', $this->articleId)
            ->whereNull('revoked_at')
            ->orderBy('role')
            ->orderBy('id')
            ->limit(50)
            ->get()
            ->map(static fn (KnowledgeArticleCollaborator $collaborator): array => [
                'id' => $collaborator->id,
                'name' => $collaborator->attribution_name ?? $collaborator->user->name,
                'email' => $collaborator->user->email,
                'role' => $collaborator->role->value,
                'role_label' => $collaborator->role->label(),
            ])
            ->all();
    }

    /** @return list<array<string, int|string|null>> */
    #[Computed]
    public function corrections(): array
    {
        if ($this->articleId === null) {
            return [];
        }

        return KnowledgeCorrection::query()
            ->select([
                'id',
                'article_id',
                'reporter_user_id',
                'field',
                'suggestion',
                'source_url',
                'status',
                'base_version_number',
                'created_at',
            ])
            ->with('reporter:id,name')
            ->where('article_id', $this->articleId)
            ->whereIn('status', [
                KnowledgeCorrectionStatus::Submitted->value,
                KnowledgeCorrectionStatus::Accepted->value,
            ])
            ->latest('created_at')
            ->limit(30)
            ->get()
            ->map(static fn (KnowledgeCorrection $correction): array => [
                'id' => $correction->id,
                'reporter' => $correction->reporter->name,
                'field' => $correction->field,
                'suggestion' => $correction->suggestion,
                'source_url' => $correction->source_url,
                'status' => $correction->status->value,
                'status_label' => $correction->status->label(),
                'base_version' => $correction->base_version_number,
            ])
            ->all();
    }

    /** @return array<int, string> */
    #[Computed]
    public function replacementOptions(): array
    {
        return KnowledgeArticle::query()
            ->forLibrary()
            ->when(
                $this->articleId !== null,
                fn ($query) => $query->whereKeyNot($this->articleId),
            )
            ->orderBy('title')
            ->limit(50)
            ->pluck('title', 'id')
            ->all();
    }

    #[Computed]
    public function canManageWorkflow(): bool
    {
        return $this->articleId !== null
            && $this->gate->forUser($this->user())
                ->allows('manageWorkflow', $this->article());
    }

    public function save(): void
    {
        $this->validateCategory();
        $data = $this->form->data($this->articleLockVersion);

        if ($this->articleId === null) {
            $article = $this->createAction->handle($this->user(), $data);
            $this->redirectRoute('knowledge.guides.edit', $article, navigate: true);

            return;
        }

        $article = $this->saveAction->handle($this->user(), $this->article(), $data);
        $this->syncArticle($article);
        $this->form->changeSummary = '';
        $this->feedback = __('knowledge.feedback.saved');
    }

    public function applyWorkflowTransition(): void
    {
        $validated = $this->validate([
            'workflowStatus' => ['required', Rule::enum(KnowledgeStatus::class)],
            'workflowReason' => ['required', 'string', 'min:10', 'max:500'],
            'replacementArticleId' => ['nullable', 'integer', 'exists:knowledge_articles,id'],
        ]);
        $replacement = isset($validated['replacementArticleId'])
            ? KnowledgeArticle::query()
                ->forEditor()
                ->findOrFail((int) $validated['replacementArticleId'])
            : null;
        $article = $this->transitionAction->handle(
            $this->user(),
            $this->article(),
            KnowledgeStatus::from((string) $validated['workflowStatus']),
            (string) $validated['workflowReason'],
            $this->articleLockVersion,
            $replacement,
        );

        $this->syncArticle($article);
        $this->workflowReason = '';
        $this->workflowStatus = array_key_first($this->workflowOptions()) ?? '';
        $this->feedback = __('knowledge.feedback.status_updated');
    }

    public function addCollaborator(): void
    {
        $validated = $this->validate([
            'collaboratorEmail' => ['required', 'email:rfc', 'max:255', 'exists:users,email'],
            'collaboratorRole' => [
                'required',
                Rule::enum(KnowledgeCollaboratorRole::class),
            ],
        ]);
        $collaborator = User::query()
            ->select(['id', 'name', 'email', 'status'])
            ->where('email', $validated['collaboratorEmail'])
            ->firstOrFail();
        $this->manageCollaboratorAction->grant(
            $this->user(),
            $this->article(),
            $collaborator,
            KnowledgeCollaboratorRole::from((string) $validated['collaboratorRole']),
            $collaborator->name,
        );

        $this->collaboratorEmail = '';
        $this->feedback = __('knowledge.feedback.collaborator_added');
        unset($this->collaborators);
    }

    public function revokeCollaborator(int $collaboratorId): void
    {
        $collaborator = KnowledgeArticleCollaborator::query()
            ->where('article_id', $this->article()->id)
            ->findOrFail($collaboratorId);
        $this->manageCollaboratorAction->revoke($this->user(), $collaborator);
        $this->feedback = __('knowledge.feedback.collaborator_removed');
        unset($this->collaborators);
    }

    public function reviewCorrection(): void
    {
        $validated = $this->validate([
            'selectedCorrectionId' => [
                'required',
                'integer',
                Rule::exists('knowledge_corrections', 'id')
                    ->where('article_id', $this->article()->id),
            ],
            'correctionDecision' => [
                'required',
                Rule::in([
                    KnowledgeCorrectionStatus::Accepted->value,
                    KnowledgeCorrectionStatus::Rejected->value,
                    KnowledgeCorrectionStatus::Applied->value,
                ]),
            ],
            'correctionReason' => ['required', 'string', 'min:10', 'max:500'],
        ]);
        $correction = KnowledgeCorrection::query()
            ->where('article_id', $this->article()->id)
            ->findOrFail((int) $validated['selectedCorrectionId']);
        $this->reviewCorrectionAction->handle(
            $this->user(),
            $correction,
            KnowledgeCorrectionStatus::from((string) $validated['correctionDecision']),
            (string) $validated['correctionReason'],
        );

        $this->selectedCorrectionId = null;
        $this->correctionReason = '';
        $this->syncArticle($this->article());
        $this->feedback = __('knowledge.feedback.correction_reviewed');
        unset($this->corrections);
    }

    public function rollback(): void
    {
        $validated = $this->validate([
            'rollbackVersionId' => [
                'required',
                'integer',
                Rule::exists('knowledge_versions', 'id')
                    ->where('article_id', $this->article()->id),
            ],
            'rollbackReason' => ['required', 'string', 'min:10', 'max:240'],
        ]);
        $version = KnowledgeVersion::query()
            ->where('article_id', $this->article()->id)
            ->findOrFail((int) $validated['rollbackVersionId']);
        $article = $this->rollbackAction->handle(
            $this->user(),
            $this->article(),
            $version,
            (string) $validated['rollbackReason'],
            $this->articleLockVersion,
        );

        $this->syncArticle($article);
        $this->form->fillFromArticle($article);
        $this->rollbackVersionId = null;
        $this->rollbackReason = '';
        $this->feedback = __('knowledge.feedback.rolled_back');
        unset($this->versions);
    }

    public function setEditorialLock(bool $locked): void
    {
        if ($locked) {
            $this->validate([
                'editorialLockReason' => ['required', 'string', 'min:10', 'max:500'],
            ]);
        }

        $article = $this->setLockAction->handle(
            $this->user(),
            $this->article(),
            $locked,
            $this->editorialLockReason,
        );
        $this->syncArticle($article);
        $this->editorialLockReason = '';
        $this->feedback = $locked
            ? __('knowledge.feedback.locked')
            : __('knowledge.feedback.unlocked');
    }

    public function render(): View
    {
        return view('livewire.forum.knowledge-guide-editor');
    }

    private function article(): KnowledgeArticle
    {
        return KnowledgeArticle::query()
            ->forEditor()
            ->findOrFail($this->articleId);
    }

    private function user(): User
    {
        $user = $this->auth->guard()->user();
        abort_unless($user instanceof User && $user->isActive(), 403);

        return $user;
    }

    private function validateCategory(): void
    {
        if (! in_array($this->form->category, $this->taxonomy->acceptedCategoryKeys(), true)) {
            throw ValidationException::withMessages([
                'form.category' => __('knowledge.validation.invalid_category'),
            ]);
        }
    }

    private function syncArticle(KnowledgeArticle $article): void
    {
        $this->articleLockVersion = $article->lock_version;
        unset(
            $this->articleData,
            $this->workflowOptions,
            $this->replacementOptions,
        );
    }

    private function canTransition(
        User $user,
        KnowledgeArticle $article,
        KnowledgeStatus $target,
    ): bool {
        $ability = match ($target) {
            KnowledgeStatus::SubmittedForReview => 'update',
            KnowledgeStatus::CommunityReviewed => null,
            KnowledgeStatus::ExpertReviewed => null,
            default => 'manageWorkflow',
        };

        if ($target === KnowledgeStatus::CommunityReviewed) {
            return $this->policy->communityReview($user, $article);
        }

        if ($target === KnowledgeStatus::ExpertReviewed) {
            return $this->policy->expertReview($user, $article);
        }

        if ($ability === null) {
            return false;
        }

        return $this->gate->forUser($user)->allows($ability, $article);
    }
}
