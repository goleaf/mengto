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
            default => throw ValidationException::withMessages(['action' => 'Unsupported profile action.']),
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
            ? ($engagement->is_saved ? 'Specialist saved.' : 'Specialist removed from saved profiles.')
            : ($engagement->is_subscribed ? 'Profile updates enabled.' : 'Profile updates disabled.');
    }

    private function submitVerification(ExpertProfile $profile): string
    {
        if ($profile->owner_key !== $this->actor->key()) {
            throw ValidationException::withMessages(['action' => 'Only the profile owner can request verification.']);
        }

        if (! $profile->credentials()->exists()) {
            throw ValidationException::withMessages(['action' => 'Add at least one qualification document first.']);
        }

        $profile->update(['verification_status' => VerificationStatus::Submitted]);
        $this->audit($profile, 'verification.submitted', []);

        return 'Verification request submitted.';
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

        return 'Report submitted for review. Your identity is not shared with the specialist.';
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
