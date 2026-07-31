<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\MentorshipRequestData;
use App\Enums\ForumMentorProfileState;
use App\Enums\ForumMentorshipEventType;
use App\Enums\ForumMentorshipState;
use App\Models\ForumMentorProfile;
use App\Models\ForumMentorScope;
use App\Models\ForumMentorship;
use App\Models\User;
use App\Services\MentorshipAudit;
use App\Services\MentorshipEligibility;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class RequestMentorship
{
    public function __construct(
        private MentorshipEligibility $eligibility,
        private MentorshipAudit $audit,
    ) {}

    public function handle(
        User $mentee,
        ForumMentorScope $scope,
        MentorshipRequestData $data,
    ): ForumMentorship {
        Validator::make([
            'message' => $data->message,
            'language' => $data->language,
            'location_scope' => $data->locationScope,
            'communication_preference' => $data->communicationPreference,
            'idempotency_key' => $data->idempotencyKey,
        ], [
            'message' => ['required', 'string', 'min:20', 'max:3000'],
            'language' => [
                'required',
                Rule::in(config('platform.supported_locales', [])),
            ],
            'location_scope' => ['nullable', 'string', 'max:160', 'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/'],
            'communication_preference' => ['required', Rule::in(['platform'])],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();

        if (! $mentee->isActive() || ! $mentee->hasVerifiedEmail()) {
            throw new AuthorizationException;
        }

        if (! $data->safetyAcknowledged) {
            throw ValidationException::withMessages([
                'safetyAcknowledged' => __('forum_mentorship.validation.safety_required'),
            ]);
        }

        $existing = ForumMentorship::query()
            ->where('idempotency_key', $data->idempotencyKey)
            ->first();

        if ($existing instanceof ForumMentorship) {
            if (
                $existing->mentee_user_id !== $mentee->id
                || $existing->forum_mentor_scope_id !== $scope->id
            ) {
                throw ValidationException::withMessages([
                    'idempotency_key' => __('forum_mentorship.validation.idempotency_conflict'),
                ]);
            }

            return $existing;
        }

        return DB::transaction(function () use ($data, $mentee, $scope): ForumMentorship {
            $lockedScope = ForumMentorScope::query()
                ->with('profile.user')
                ->lockForUpdate()
                ->findOrFail($scope->id);
            $profile = ForumMentorProfile::query()
                ->whereKey($lockedScope->forum_mentor_profile_id)
                ->lockForUpdate()
                ->firstOrFail();
            $mentor = $lockedScope->profile->user;

            if (
                ! $lockedScope->is_active
                || $profile->state !== ForumMentorProfileState::Active
                || ! $profile->is_public
                || $profile->safety_acknowledged_at === null
                || ! $mentor->isActive()
                || ! $mentor->hasVerifiedEmail()
            ) {
                throw ValidationException::withMessages([
                    'scope' => __('forum_mentorship.validation.scope_unavailable'),
                ]);
            }

            if ($mentor->id === $mentee->id) {
                throw ValidationException::withMessages([
                    'scope' => __('forum_mentorship.validation.self_request'),
                ]);
            }

            if ($this->eligibility->usersBlockEachOther($mentee, $mentor)) {
                throw new AuthorizationException;
            }

            if (
                ! in_array($data->language, $profile->languages, true)
                || ! in_array(
                    $data->communicationPreference,
                    $profile->communication_preferences,
                    true,
                )
            ) {
                throw ValidationException::withMessages([
                    'scope' => __('forum_mentorship.validation.match_changed'),
                ]);
            }

            if (
                $lockedScope->requires_verified_expertise
                && ! $this->eligibility->hasCurrentProfessionalVerification($mentor)
            ) {
                throw ValidationException::withMessages([
                    'scope' => __('forum_mentorship.validation.professional_scope_unavailable'),
                ]);
            }

            if (! $this->eligibility->profileHasRequestCapacity($profile)) {
                throw ValidationException::withMessages([
                    'scope' => __('forum_mentorship.validation.capacity_reached'),
                ]);
            }

            $openKey = hash('sha256', implode('|', [
                (string) $mentor->id,
                (string) $mentee->id,
                (string) $lockedScope->id,
            ]));

            if (ForumMentorship::query()->where('open_key', $openKey)->exists()) {
                throw ValidationException::withMessages([
                    'request' => __('forum_mentorship.validation.duplicate_open_request'),
                ]);
            }

            $mentorship = ForumMentorship::query()->create([
                'forum_mentor_scope_id' => $lockedScope->id,
                'mentor_user_id' => $mentor->id,
                'mentee_user_id' => $mentee->id,
                'mentorship_type' => $lockedScope->mentorship_type,
                'state' => ForumMentorshipState::Requested,
                'language' => $data->language,
                'location_scope' => $data->locationScope,
                'communication_preference' => $data->communicationPreference,
                'request_message' => trim($data->message),
                'mentee_safety_acknowledged_at' => now(),
                'requested_at' => now(),
                'lock_version' => 0,
                'open_key' => $openKey,
                'idempotency_key' => $data->idempotencyKey,
                'metadata' => [
                    'boundaries_translation_key' => 'forum_mentorship.safety.boundaries',
                ],
            ]);

            $this->audit->record(
                mentorship: $mentorship,
                actor: $mentee,
                eventType: ForumMentorshipEventType::Requested,
                reasonCode: 'mentorship-requested',
                summaryTranslationKey: 'forum_mentorship.events.requested',
                toState: ForumMentorshipState::Requested,
                idempotencyKey: "mentorship:{$mentorship->id}:requested",
            );

            return $mentorship;
        }, 3);
    }
}
