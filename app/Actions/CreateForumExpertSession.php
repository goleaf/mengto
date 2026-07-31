<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CreateForumExpertSessionData;
use App\Enums\ForumExpertSessionStatus;
use App\Models\ExpertProfile;
use App\Models\ForumExpertSession;
use App\Models\User;
use App\Services\ForumExpertSessionAudit;
use App\Services\ForumExpertSessionHostEligibility;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class CreateForumExpertSession
{
    public function __construct(
        private Gate $gate,
        private ForumExpertSessionHostEligibility $eligibility,
        private ForumExpertSessionAudit $audit,
    ) {}

    public function handle(User $actor, CreateForumExpertSessionData $data): ForumExpertSession
    {
        $this->gate->forUser($actor)->authorize('create', ForumExpertSession::class);
        $this->validate($data);

        $existing = ForumExpertSession::query()
            ->where('creation_idempotency_key', $data->idempotencyKey)
            ->first();

        if ($existing !== null) {
            if (! $existing->isHost($actor)) {
                throw ValidationException::withMessages([
                    'form.title' => __('forum_expert_sessions.validation.idempotency_conflict'),
                ]);
            }

            return $existing;
        }

        $profile = ExpertProfile::query()
            ->select([
                'id',
                'owner_id',
                'public_name',
                'primary_type',
                'specializations',
                'country',
                'status',
                'verification_status',
                'verification_expires_at',
            ])
            ->findOrFail($data->expertProfileId);

        if (! $this->eligibility->allows(
            $actor,
            $profile,
            $data->professionalScope,
            $data->jurisdiction,
        )) {
            throw new AuthorizationException(
                __('forum_expert_sessions.validation.host_not_eligible'),
            );
        }

        return DB::transaction(function () use ($actor, $data, $profile): ForumExpertSession {
            $session = ForumExpertSession::query()->createOrFirst(
                ['creation_idempotency_key' => $data->idempotencyKey],
                [
                    'expert_profile_id' => $profile->id,
                    'created_by_user_id' => $actor->id,
                    'stable_key' => Str::slug($data->title).'-'.Str::lower((string) Str::ulid()),
                    'host_name_snapshot' => $profile->public_name,
                    'professional_scope' => trim($data->professionalScope),
                    'jurisdiction' => strtoupper(trim($data->jurisdiction)),
                    'title' => trim($data->title),
                    'summary' => trim($data->summary),
                    'locale' => $data->locale,
                    'timezone' => $data->timezone,
                    'status' => ForumExpertSessionStatus::Published,
                    'disclaimer_version' => '2026-07',
                    'question_opens_at' => $data->questionOpensAt->setTimezone('UTC'),
                    'question_closes_at' => $data->questionClosesAt->setTimezone('UTC'),
                    'starts_at' => $data->startsAt->setTimezone('UTC'),
                    'ends_at' => $data->endsAt->setTimezone('UTC'),
                ],
            );

            if (! $session->wasRecentlyCreated) {
                if (! $session->isHost($actor)) {
                    throw ValidationException::withMessages([
                        'form.title' => __('forum_expert_sessions.validation.idempotency_conflict'),
                    ]);
                }

                return $session;
            }

            $this->audit->record(
                session: $session,
                actor: $actor,
                eventType: 'created',
                reasonCode: 'session-created',
                summaryTranslationKey: 'forum_expert_sessions.history.created',
                toStatus: ForumExpertSessionStatus::Published->value,
                metadata: [
                    'professional_scope' => $session->professional_scope,
                    'jurisdiction' => $session->jurisdiction,
                    'disclaimer_version' => $session->disclaimer_version,
                ],
                idempotencyKey: 'expert-session:create:'.$data->idempotencyKey,
            );

            return $session;
        }, 3);
    }

    private function validate(CreateForumExpertSessionData $data): void
    {
        Validator::make([
            'expert_profile_id' => $data->expertProfileId,
            'professional_scope' => trim($data->professionalScope),
            'jurisdiction' => strtoupper(trim($data->jurisdiction)),
            'title' => trim($data->title),
            'summary' => trim($data->summary),
            'locale' => $data->locale,
            'timezone' => $data->timezone,
            'question_opens_at' => $data->questionOpensAt->toAtomString(),
            'question_closes_at' => $data->questionClosesAt->toAtomString(),
            'starts_at' => $data->startsAt->toAtomString(),
            'ends_at' => $data->endsAt->toAtomString(),
            'idempotency_key' => $data->idempotencyKey,
        ], [
            'expert_profile_id' => ['required', 'integer', 'min:1'],
            'professional_scope' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'jurisdiction' => ['required', 'string', 'max:120', 'regex:/^[A-Z0-9][A-Z0-9._-]*$/'],
            'title' => ['required', 'string', 'min:8', 'max:180'],
            'summary' => ['required', 'string', 'min:20', 'max:10000'],
            'locale' => ['required', Rule::in(config('platform.supported_locales', ['en']))],
            'timezone' => ['required', 'timezone:all'],
            'question_opens_at' => ['required', 'date'],
            'question_closes_at' => ['required', 'date', 'after:question_opens_at'],
            'starts_at' => ['required', 'date', 'after_or_equal:question_opens_at'],
            'ends_at' => ['required', 'date', 'after:starts_at', 'after_or_equal:question_closes_at'],
            'idempotency_key' => ['required', 'uuid'],
        ])->validate();
    }
}
