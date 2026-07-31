<?php

declare(strict_types=1);

namespace App\Livewire\Forum;

use App\Actions\SaveMentorScope;
use App\Actions\UpdateMentorProfile;
use App\Enums\ForumMentorProfileState;
use App\Enums\ForumMentorshipType;
use App\Livewire\Forms\MentorProfileForm;
use App\Livewire\Forms\MentorScopeForm;
use App\Models\ForumMentorProfile;
use App\Models\ForumMentorScope;
use App\Models\User;
use App\Services\ForumCategoryTree;
use App\Services\MentorshipEligibility;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class MentorProfileManager extends Component
{
    public MentorProfileForm $profileForm;

    public MentorScopeForm $scopeForm;

    public string $feedback = '';

    private AuthFactory $auth;

    private MentorshipEligibility $eligibility;

    private ForumCategoryTree $categoryTree;

    private SaveMentorScope $saveScopeAction;

    private UpdateMentorProfile $updateProfileAction;

    public function boot(
        AuthFactory $auth,
        MentorshipEligibility $eligibility,
        ForumCategoryTree $categoryTree,
        UpdateMentorProfile $updateProfileAction,
        SaveMentorScope $saveScopeAction,
    ): void {
        $this->auth = $auth;
        $this->eligibility = $eligibility;
        $this->categoryTree = $categoryTree;
        $this->updateProfileAction = $updateProfileAction;
        $this->saveScopeAction = $saveScopeAction;
    }

    public function mount(): void
    {
        $profile = $this->profileModel();

        if ($profile instanceof ForumMentorProfile) {
            $this->profileForm->fillFromProfile($profile);
        } else {
            $user = $this->requireUser();
            $this->profileForm->timezone = $user->timezone;
            $this->profileForm->languages = [$user->locale];
        }
    }

    #[Computed]
    public function canActivate(): bool
    {
        return $this->eligibility->canActivateProfile($this->requireUser());
    }

    /** @return array<string, string> */
    #[Computed]
    public function profileStateOptions(): array
    {
        return collect(ForumMentorProfileState::cases())
            ->mapWithKeys(static fn (ForumMentorProfileState $state): array => [
                $state->value => $state->label(),
            ])
            ->all();
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
    public function scopes(): array
    {
        $profile = $this->profileModel();

        if (! $profile instanceof ForumMentorProfile) {
            return [];
        }

        return $profile->scopes()
            ->select([
                'id',
                'forum_mentor_profile_id',
                'mentorship_type',
                'forum_category_id',
                'taxon_id',
                'experience_summary',
                'requires_verified_expertise',
                'is_active',
            ])
            ->with(['category.translations', 'taxon.activeVersion'])
            ->orderBy('id')
            ->limit(25)
            ->get()
            ->map(static fn (ForumMentorScope $scope): array => [
                'id' => $scope->id,
                'type' => $scope->mentorship_type->label(),
                'experience' => $scope->experience_summary,
                'category' => $scope->category?->translations
                    ->firstWhere('locale', app()->getLocale())?->name,
                'taxon' => $scope->taxon?->activeVersion?->scientific_name,
                'professional' => $scope->requires_verified_expertise,
                'active' => $scope->is_active,
            ])
            ->all();
    }

    public function saveProfile(): void
    {
        $profile = $this->profileModel();
        $lockVersion = $profile instanceof ForumMentorProfile
            ? $profile->lock_version
            : 0;
        $updated = $this->updateProfileAction->handle(
            $this->requireUser(),
            $this->profileForm->data($lockVersion),
        );
        $this->profileForm->fillFromProfile($updated);
        $this->feedback = __('forum_mentorship.feedback.profile_saved');
        unset($this->canActivate, $this->scopes);
    }

    public function saveScope(): void
    {
        $profile = $this->profileModel();

        if (! $profile instanceof ForumMentorProfile) {
            $this->addError('scope', __('forum_mentorship.validation.profile_required'));

            return;
        }

        $this->saveScopeAction->handle(
            $this->requireUser(),
            $profile,
            $this->scopeForm->data(),
        );
        $this->scopeForm->reset();
        $this->feedback = __('forum_mentorship.feedback.scope_saved');
        unset($this->scopes);
    }

    public function render(): View
    {
        return view('livewire.forum.mentor-profile-manager');
    }

    private function profileModel(): ?ForumMentorProfile
    {
        return ForumMentorProfile::query()
            ->where('user_id', $this->requireUser()->id)
            ->first();
    }

    private function requireUser(): User
    {
        $user = $this->auth->user();

        abort_unless($user instanceof User && $user->isActive(), 403);

        return $user;
    }
}
