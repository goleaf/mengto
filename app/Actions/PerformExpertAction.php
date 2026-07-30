<?php

namespace App\Actions;

use App\Enums\VerificationStatus;
use App\Models\AuditLog;
use App\Models\ExpertEngagement;
use App\Models\ExpertProfile;
use App\Models\ExpertReport;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PerformExpertAction
{
    public function __construct(private readonly ForumActor $actor) {}

    /** @param array<string, mixed> $data */
    public function handle(ExpertProfile $profile, array $data): string
    {
        return DB::transaction(fn (): string => match ($data['action']) {
            'toggle-save' => $this->toggle($profile, 'is_saved'),
            'toggle-subscribe' => $this->toggle($profile, 'is_subscribed'),
            'submit-verification' => $this->submitVerification($profile),
            'report' => $this->report($profile, $data),
            default => throw ValidationException::withMessages(['action' => __('messages.unsupported_profile_action_ae2cc1fae5')]),
        });
    }

    private function toggle(ExpertProfile $profile, string $field): string
    {
        $engagement = ExpertEngagement::query()->firstOrCreate(
            ['expert_profile_id' => $profile->id, 'user_key' => $this->actor->key()],
            ['is_saved' => false, 'is_subscribed' => false],
        );
        $engagement->update([$field => ! $engagement->{$field}, 'last_viewed_at' => now()]);

        return $field === 'is_saved'
            ? ($engagement->is_saved ? __('messages.specialist_saved_390e2f1b76') : __('messages.specialist_removed_from_saved_profiles_c8d73d025b'))
            : ($engagement->is_subscribed ? __('messages.profile_updates_enabled_25136ab770') : __('messages.profile_updates_disabled_d2fa778c98'));
    }

    private function submitVerification(ExpertProfile $profile): string
    {
        if ($profile->owner_key !== $this->actor->key()) {
            throw ValidationException::withMessages(['action' => __('messages.only_the_profile_owner_can_request_verification_880cface2c')]);
        }

        if (! $profile->credentials()->exists()) {
            throw ValidationException::withMessages(['action' => __('messages.add_at_least_one_qualification_document_first_127b03e81a')]);
        }

        $profile->update(['verification_status' => VerificationStatus::Submitted]);
        $this->audit($profile, 'verification.submitted', []);

        return __('messages.verification_request_submitted_928a3a312b');
    }

    /** @param array<string, mixed> $data */
    private function report(ExpertProfile $profile, array $data): string
    {
        $highPriorityReasons = [
            'dangerous-advice',
            'false-qualification',
            'forged-document',
            'animal-cruelty',
            'medical-data-exposure',
        ];
        $report = ExpertReport::query()->create([
            'expert_profile_id' => $profile->id,
            'reporter_key' => $this->actor->key(),
            'reason' => $data['reason'],
            'details' => $data['details'] ?? null,
            'priority' => in_array($data['reason'], $highPriorityReasons, true) ? 'high' : 'normal',
            'status' => 'submitted',
        ]);
        $this->audit($profile, 'expert-report.submitted', ['report_id' => $report->id]);

        return __('messages.report_submitted_for_review_your_identity_is_not_shared__af00537fb0');
    }

    /** @param array<string, mixed> $metadata */
    private function audit(ExpertProfile $profile, string $action, array $metadata): void
    {
        AuditLog::query()->create([
            'expert_profile_id' => $profile->id,
            'actor_key' => $this->actor->key(),
            'actor_role' => $profile->owner_key === $this->actor->key() ? 'profile-owner' : 'client',
            'action' => $action,
            'target_type' => ExpertProfile::class,
            'target_id' => (string) $profile->id,
            'metadata' => $metadata,
        ]);
    }
}
