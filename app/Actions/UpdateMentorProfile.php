<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\MentorProfileData;
use App\Enums\ForumMentorProfileState;
use App\Models\ForumMentorProfile;
use App\Models\User;
use App\Services\MentorshipEligibility;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class UpdateMentorProfile
{
    public function __construct(private MentorshipEligibility $eligibility) {}

    public function handle(User $user, MentorProfileData $data): ForumMentorProfile
    {
        $supportedLocales = config('platform.supported_locales', []);
        Validator::make([
            'state' => $data->state->value,
            'headline' => $data->headline,
            'summary' => $data->summary,
            'languages' => $data->languages,
            'location_scope' => $data->locationScope,
            'timezone' => $data->timezone,
            'communication_preferences' => $data->communicationPreferences,
            'availability' => $data->availability,
            'capacity' => $data->capacity,
            'expected_lock_version' => $data->expectedLockVersion,
        ], [
            'state' => ['required', Rule::enum(ForumMentorProfileState::class)],
            'headline' => ['required', 'string', 'min:5', 'max:160'],
            'summary' => ['required', 'string', 'min:20', 'max:3000'],
            'languages' => ['required', 'array', 'min:1', 'max:3'],
            'languages.*' => ['required', 'string', Rule::in($supportedLocales)],
            'location_scope' => ['nullable', 'string', 'max:160', 'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/'],
            'timezone' => ['required', 'string', Rule::in(timezone_identifiers_list())],
            'communication_preferences' => ['required', 'array', 'size:1'],
            'communication_preferences.*' => ['required', Rule::in(['platform'])],
            'availability' => ['array', 'max:14'],
            'capacity' => ['required', 'integer', 'min:1', 'max:10'],
            'expected_lock_version' => ['required', 'integer', 'min:0'],
        ])->validate();

        if (! $user->isActive() || ! $user->hasVerifiedEmail()) {
            throw new AuthorizationException;
        }

        if (
            $data->state === ForumMentorProfileState::Active
            && ! $this->eligibility->canActivateProfile($user)
        ) {
            throw new AuthorizationException;
        }

        return DB::transaction(function () use ($data, $user): ForumMentorProfile {
            $profile = ForumMentorProfile::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (
                $profile instanceof ForumMentorProfile
                && $profile->lock_version !== $data->expectedLockVersion
            ) {
                throw ValidationException::withMessages([
                    'profile' => __('forum_mentorship.validation.stale_profile'),
                ]);
            }

            if (
                $data->state === ForumMentorProfileState::Active
                && ! $data->safetyAcknowledged
                && $profile?->safety_acknowledged_at === null
            ) {
                throw ValidationException::withMessages([
                    'safetyAcknowledged' => __('forum_mentorship.validation.safety_required'),
                ]);
            }

            $existingSafetyAcknowledgement = $profile instanceof ForumMentorProfile
                ? $profile->safety_acknowledged_at
                : null;
            $nextLockVersion = $profile instanceof ForumMentorProfile
                ? $profile->lock_version + 1
                : 1;
            $values = [
                'state' => $data->state,
                'headline' => trim($data->headline),
                'summary' => trim($data->summary),
                'languages' => array_values(array_unique($data->languages)),
                'location_scope' => $data->locationScope,
                'timezone' => $data->timezone,
                'communication_preferences' => array_values(array_unique(
                    $data->communicationPreferences,
                )),
                'availability' => $data->availability,
                'capacity' => $data->capacity,
                'is_public' => $data->isPublic,
                'safety_acknowledged_at' => $data->safetyAcknowledged
                    ? ($existingSafetyAcknowledgement ?? now())
                    : $existingSafetyAcknowledgement,
                'lock_version' => $nextLockVersion,
            ];

            if ($profile instanceof ForumMentorProfile) {
                $profile->update($values);
            } else {
                $profile = ForumMentorProfile::query()->create([
                    'user_id' => $user->id,
                    ...$values,
                ]);
            }

            if ($data->state === ForumMentorProfileState::Withdrawn) {
                $profile->scopes()->update(['is_active' => false]);
            }

            return $profile->refresh();
        }, 3);
    }
}
