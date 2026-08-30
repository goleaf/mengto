<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\UserStatus;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\EmailVerificationMode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use LogicException;
use RuntimeException;

final readonly class ActivatePendingEmailUsers
{
    private const string AUDIT_ACTION = 'account.email-verification-bypassed';

    private const string AUDIT_ACTOR = 'system-email-verification-mode';

    public function __construct(private EmailVerificationMode $emailVerification) {}

    /** @return array{eligible: int, activated: int} */
    public function handle(bool $dryRun = false, int $chunkSize = 200): array
    {
        $chunkSize = max(1, min(1000, $chunkSize));
        $eligible = $this->eligibleUsers()->count();

        if ($dryRun) {
            return ['eligible' => $eligible, 'activated' => 0];
        }

        if ($this->emailVerification->isEnabled()) {
            throw new LogicException(__('auth.email_verification_activation.enabled_error'));
        }

        $activated = 0;

        $this->eligibleUsers()
            ->select('id')
            ->chunkById($chunkSize, function (Collection $users) use (&$activated): void {
                DB::transaction(function () use ($users, &$activated): void {
                    $candidateIds = $users->modelKeys();
                    $lockedIds = User::query()
                        ->select('id')
                        ->whereKey($candidateIds)
                        ->where('status', UserStatus::Active)
                        ->whereNull('email_verified_at')
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->pluck('id')
                        ->map(static fn (mixed $id): int => (int) $id)
                        ->all();

                    if ($lockedIds === []) {
                        return;
                    }

                    $timestamp = now();
                    $updated = User::query()
                        ->whereKey($lockedIds)
                        ->where('status', UserStatus::Active)
                        ->whereNull('email_verified_at')
                        ->update([
                            'email_verified_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ]);

                    if ($updated !== count($lockedIds)) {
                        throw new RuntimeException(
                            __('auth.email_verification_activation.changed_unexpectedly'),
                        );
                    }

                    $metadata = json_encode(
                        ['reason' => 'email-verification-disabled'],
                        JSON_THROW_ON_ERROR,
                    );

                    AuditLog::query()->insert(array_map(
                        static fn (int $userId): array => [
                            'actor_key' => self::AUDIT_ACTOR,
                            'actor_role' => 'system',
                            'action' => self::AUDIT_ACTION,
                            'target_type' => User::class,
                            'target_id' => (string) $userId,
                            'metadata' => $metadata,
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ],
                        $lockedIds,
                    ));

                    $activated += $updated;
                }, 3);
            });

        return ['eligible' => $eligible, 'activated' => $activated];
    }

    /** @return Builder<User> */
    private function eligibleUsers(): Builder
    {
        return User::query()
            ->where('status', UserStatus::Active)
            ->whereNull('email_verified_at');
    }
}
