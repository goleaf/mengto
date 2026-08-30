<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\PlaceReview;
use App\Models\PlaceReviewResponse;
use App\Models\PlaceReviewResponseVersion;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class UpsertPlaceReviewResponse
{
    public function __construct(private Gate $gate) {}

    public function handle(
        User $actor,
        PlaceReview $review,
        string $body,
        string $idempotencyKey,
        ?string $reason,
    ): PlaceReviewResponse {
        /** @var array{body: string, idempotency_key: string, reason: string|null} $validated */
        $validated = validator([
            'body' => trim($body),
            'idempotency_key' => $idempotencyKey,
            'reason' => $reason === null ? null : trim($reason),
        ], [
            'body' => ['required', 'string', 'min:10', 'max:4000'],
            'idempotency_key' => ['required', 'uuid'],
            'reason' => ['nullable', 'string', 'min:5', 'max:1000'],
        ])->validate();

        $review->loadMissing('place');
        $this->gate->forUser($actor)->authorize('respond', [PlaceReviewResponse::class, $review]);

        $replay = PlaceReviewResponse::query()
            ->where('author_user_id', $actor->id)
            ->where('idempotency_key', $validated['idempotency_key'])
            ->first();
        if ($replay instanceof PlaceReviewResponse) {
            return $this->validatedResponseReplay($replay, $review, $validated['body']);
        }

        $versionReplay = PlaceReviewResponseVersion::query()
            ->where('editor_user_id', $actor->id)
            ->where('idempotency_key', $validated['idempotency_key'])
            ->first();
        if ($versionReplay instanceof PlaceReviewResponseVersion) {
            if ($versionReplay->body !== $validated['body'] || $versionReplay->reason !== $validated['reason']) {
                throw ValidationException::withMessages(['place_idempotency_key' => __('validation.prohibited')]);
            }

            return PlaceReviewResponse::query()
                ->with('versions')
                ->findOrFail($versionReplay->place_review_response_id);
        }

        return DB::transaction(function () use ($actor, $review, $validated): PlaceReviewResponse {
            $lockedReview = PlaceReview::query()
                ->with(['place.organization', 'response'])
                ->lockForUpdate()
                ->findOrFail($review->id);
            $this->gate->forUser($actor)->authorize('respond', [PlaceReviewResponse::class, $lockedReview]);
            $response = $lockedReview->response;

            if ($response === null) {
                $response = PlaceReviewResponse::query()->create([
                    'place_review_id' => $lockedReview->id,
                    'author_user_id' => $actor->id,
                    'stable_key' => 'place-review-response-'.Str::lower((string) Str::ulid()),
                    'idempotency_key' => $validated['idempotency_key'],
                    'body' => $validated['body'],
                    'current_version' => 1,
                ]);
                PlaceReviewResponseVersion::query()->create([
                    'place_review_response_id' => $response->id,
                    'editor_user_id' => $actor->id,
                    'idempotency_key' => $validated['idempotency_key'],
                    'version' => 1,
                    'body' => $response->body,
                    'reason' => $validated['reason'],
                ]);

                return $response->load('versions');
            }

            $response->load('review.place');
            $this->gate->forUser($actor)->authorize('update', $response);
            $nextVersion = $response->current_version + 1;
            $response->forceFill([
                'body' => $validated['body'],
                'current_version' => $nextVersion,
            ])->save();
            PlaceReviewResponseVersion::query()->create([
                'place_review_response_id' => $response->id,
                'editor_user_id' => $actor->id,
                'idempotency_key' => $validated['idempotency_key'],
                'version' => $nextVersion,
                'body' => $response->body,
                'reason' => $validated['reason'],
            ]);

            return $response->load('versions');
        }, 3);
    }

    private function validatedResponseReplay(
        PlaceReviewResponse $response,
        PlaceReview $review,
        string $body,
    ): PlaceReviewResponse {
        if ($response->place_review_id !== $review->id || $response->body !== $body) {
            throw ValidationException::withMessages(['place_idempotency_key' => __('validation.prohibited')]);
        }

        return $response->loadMissing('versions');
    }
}
