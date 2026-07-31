<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\MentorScopeData;
use App\Models\ForumCategory;
use App\Models\ForumMentorProfile;
use App\Models\ForumMentorScope;
use App\Models\Taxon;
use App\Models\User;
use App\Services\MentorshipEligibility;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class SaveMentorScope
{
    public function __construct(private MentorshipEligibility $eligibility) {}

    public function handle(
        User $user,
        ForumMentorProfile $profile,
        MentorScopeData $data,
    ): ForumMentorScope {
        if (! $user->isActive() || $profile->user_id !== $user->id) {
            throw new AuthorizationException;
        }

        Validator::make([
            'experience_summary' => $data->experienceSummary,
            'forum_category_id' => $data->forumCategoryId,
            'taxon_id' => $data->taxonId,
        ], [
            'experience_summary' => ['required', 'string', 'min:20', 'max:2000'],
            'forum_category_id' => [
                'nullable',
                'integer',
                'exists:forum_categories,id',
            ],
            'taxon_id' => ['nullable', 'integer', 'exists:taxa,id'],
        ])->validate();

        if (
            $data->forumCategoryId !== null
            && ! ForumCategory::query()->active()->whereKey($data->forumCategoryId)->exists()
        ) {
            throw ValidationException::withMessages([
                'forum_category_id' => __('forum_mentorship.validation.category_unavailable'),
            ]);
        }

        if (
            $data->taxonId !== null
            && ! Taxon::query()->active()->whereKey($data->taxonId)->exists()
        ) {
            throw ValidationException::withMessages([
                'taxon_id' => __('forum_mentorship.validation.taxon_unavailable'),
            ]);
        }

        if (
            $data->requiresVerifiedExpertise
            && ! $this->eligibility->hasCurrentProfessionalVerification($user)
        ) {
            throw ValidationException::withMessages([
                'requires_verified_expertise' => __(
                    'forum_mentorship.validation.professional_verification_required',
                ),
            ]);
        }

        $scopeKey = hash('sha256', implode('|', [
            (string) $profile->id,
            $data->type->value,
            (string) ($data->forumCategoryId ?? 0),
            (string) ($data->taxonId ?? 0),
        ]));

        return DB::transaction(function () use (
            $data,
            $profile,
            $scopeKey,
            $user,
        ): ForumMentorScope {
            $lockedProfile = ForumMentorProfile::query()
                ->whereKey($profile->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedProfile->user_id !== $user->id) {
                throw new AuthorizationException;
            }

            $existing = ForumMentorScope::query()
                ->where('scope_key', $scopeKey)
                ->lockForUpdate()
                ->first();

            if (
                ! $existing instanceof ForumMentorScope
                && $lockedProfile->scopes()->count() >= 25
            ) {
                throw ValidationException::withMessages([
                    'scope' => __('forum_mentorship.validation.scope_limit'),
                ]);
            }

            return ForumMentorScope::query()->updateOrCreate(
                ['scope_key' => $scopeKey],
                [
                    'forum_mentor_profile_id' => $lockedProfile->id,
                    'mentorship_type' => $data->type,
                    'forum_category_id' => $data->forumCategoryId,
                    'taxon_id' => $data->taxonId,
                    'experience_summary' => trim($data->experienceSummary),
                    'requires_verified_expertise' => $data->requiresVerifiedExpertise,
                    'is_active' => $data->isActive,
                    'metadata' => [
                        'professional_status_checked_at' => $data->requiresVerifiedExpertise
                            ? now()->toAtomString()
                            : null,
                    ],
                ],
            );
        }, 3);
    }
}
