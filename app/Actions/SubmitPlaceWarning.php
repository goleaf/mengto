<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceWarningCategory;
use App\Enums\PlaceWarningSeverity;
use App\Enums\PlaceWarningSource;
use App\Enums\PlaceWarningStatus;
use App\Models\Place;
use App\Models\PlaceWarning;
use App\Models\PlaceWarningEvent;
use App\Models\User;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class SubmitPlaceWarning
{
    public function __construct(private Gate $gate) {}

    public function handle(
        User $actor,
        Place $place,
        PlaceWarningCategory $category,
        PlaceWarningSeverity $severity,
        string $affectedScope,
        PlaceWarningSource $source,
        string $title,
        string $detail,
        ?string $evidence,
        DateTimeInterface $expiresAt,
        string $idempotencyKey,
    ): PlaceWarning {
        $this->gate->forUser($actor)->authorize('submit', [PlaceWarning::class, $place]);

        /** @var array{affected_scope: string, title: string, detail: string, evidence: string|null, idempotency_key: string} $validated */
        $validated = validator([
            'affected_scope' => trim($affectedScope),
            'title' => trim($title),
            'detail' => trim($detail),
            'evidence' => $evidence === null ? null : trim($evidence),
            'idempotency_key' => $idempotencyKey,
        ], [
            'affected_scope' => ['required', 'string', 'min:3', 'max:240'],
            'title' => ['required', 'string', 'min:5', 'max:180'],
            'detail' => ['required', 'string', 'min:20', 'max:4000'],
            'evidence' => [
                $severity->requiresModeration() ? 'required' : 'nullable',
                'string', 'min:5', 'max:4000',
            ],
            'idempotency_key' => ['required', 'uuid'],
        ])->validate();

        $expiry = Carbon::instance($expiresAt)->setMicrosecond(0)->toImmutable();
        if ($expiry->isPast() || $expiry->greaterThan(now()->addDays(30))) {
            throw ValidationException::withMessages([
                'expires_at' => __('places.validation.warning_expiry_invalid'),
            ]);
        }
        if ($this->containsExactCoordinate($validated['affected_scope'])
            || $this->containsExactCoordinate($validated['title'])
            || $this->containsExactCoordinate($validated['detail'])
            || str_contains($validated['affected_scope'].$validated['title'].$validated['detail'], '<')
            || str_contains($validated['affected_scope'].$validated['title'].$validated['detail'], '>')) {
            throw ValidationException::withMessages([
                'warning' => __('places.validation.warning_public_content_invalid'),
            ]);
        }

        $existing = PlaceWarning::query()
            ->where('author_user_id', $actor->id)
            ->where('idempotency_key', $validated['idempotency_key'])
            ->first();
        if ($existing !== null) {
            return $this->validatedReplay($existing, $actor, $place, $category, $severity, $source, $validated, $expiry);
        }

        $rateKey = 'place-warning:hour:'.hash('sha256', (string) $actor->id);
        if (RateLimiter::tooManyAttempts($rateKey, 10)) {
            throw ValidationException::withMessages([
                'warning' => __('places.validation.warning_rate_limited'),
            ]);
        }

        [$warning, $created] = DB::transaction(function () use ($actor, $place, $category, $severity, $source, $validated, $expiry): array {
            $lockedPlace = Place::query()
                ->select(['id', 'owner_user_id', 'organization_id', 'visibility', 'status', 'archived_at'])
                ->lockForUpdate()
                ->findOrFail($place->id);
            $lockedPlace->setRelation('organization', $place->organization);
            $this->gate->forUser($actor)->authorize('submit', [PlaceWarning::class, $lockedPlace]);

            $status = $severity->requiresModeration()
                ? PlaceWarningStatus::NeedsReview
                : PlaceWarningStatus::Published;
            $publishedAt = $status === PlaceWarningStatus::Published ? now() : null;
            $warning = PlaceWarning::query()->createOrFirst(
                ['author_user_id' => $actor->id, 'idempotency_key' => $validated['idempotency_key']],
                [
                    'place_id' => $lockedPlace->id,
                    'stable_key' => 'place-warning-'.Str::lower((string) Str::ulid()),
                    'category' => $category,
                    'severity' => $severity,
                    'affected_scope' => $validated['affected_scope'],
                    'source' => $source,
                    'title' => $validated['title'],
                    'detail' => $validated['detail'],
                    'evidence' => $validated['evidence'],
                    'status' => $status,
                    'published_at' => $publishedAt,
                    'expires_at' => $expiry,
                ],
            );

            if (! $warning->wasRecentlyCreated) {
                return [$this->validatedReplay($warning, $actor, $lockedPlace, $category, $severity, $source, $validated, $expiry), false];
            }

            $this->recordEvent($warning, $actor, 'submitted', null, $status, 'submitted:'.$validated['idempotency_key']);
            if ($status === PlaceWarningStatus::Published) {
                $this->recordEvent($warning, $actor, 'published', null, $status, 'published:'.$validated['idempotency_key']);
            }

            return [$warning, true];
        }, 3);

        if ($created) {
            RateLimiter::hit($rateKey, 3600);
        }

        return $warning;
    }

    /** @param array{affected_scope: string, title: string, detail: string, evidence: string|null, idempotency_key: string} $validated */
    private function validatedReplay(
        PlaceWarning $warning,
        User $actor,
        Place $place,
        PlaceWarningCategory $category,
        PlaceWarningSeverity $severity,
        PlaceWarningSource $source,
        array $validated,
        CarbonInterface $expiry,
    ): PlaceWarning {
        if (
            $warning->author_user_id !== $actor->id
            || $warning->place_id !== $place->id
            || $warning->category !== $category
            || $warning->severity !== $severity
            || $warning->source !== $source
            || $warning->affected_scope !== $validated['affected_scope']
            || $warning->title !== $validated['title']
            || $warning->detail !== $validated['detail']
            || $warning->evidence !== $validated['evidence']
            || ! $warning->expires_at->equalTo($expiry)
        ) {
            throw ValidationException::withMessages([
                'place_idempotency_key' => __('places.validation.warning_idempotency_conflict'),
            ]);
        }

        return $warning;
    }

    private function recordEvent(
        PlaceWarning $warning,
        User $actor,
        string $eventType,
        ?PlaceWarningStatus $fromStatus,
        PlaceWarningStatus $toStatus,
        string $idempotencyKey,
    ): void {
        PlaceWarningEvent::query()->createOrFirst(
            ['actor_user_id' => $actor->id, 'idempotency_key' => $idempotencyKey],
            [
                'place_warning_id' => $warning->id,
                'event_type' => $eventType,
                'from_status' => $fromStatus?->value,
                'to_status' => $toStatus->value,
                'public_summary_key' => 'places.warnings.events.'.$eventType,
            ],
        );
    }

    private function containsExactCoordinate(string $value): bool
    {
        return preg_match('/[-+]?\d{1,2}\.\d{4,}\s*,\s*[-+]?\d{1,3}\.\d{4,}/', $value) === 1;
    }
}
