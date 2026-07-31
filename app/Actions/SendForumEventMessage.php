<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumEventMessageAudience;
use App\Models\ForumEvent;
use App\Models\ForumEventMessage;
use App\Models\User;
use App\Services\ForumEventAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class SendForumEventMessage
{
    public function __construct(
        private Gate $gate,
        private ForumEventAudit $audit,
    ) {}

    public function handle(
        User $actor,
        ForumEvent $event,
        ForumEventMessageAudience $audience,
        string $body,
        string $idempotencyKey,
    ): ForumEventMessage {
        $this->gate->forUser($actor)->authorize('sendMessage', $event);
        Validator::make([
            'audience' => $audience->value,
            'body' => $body,
            'idempotency_key' => $idempotencyKey,
        ], [
            'audience' => ['required', Rule::enum(ForumEventMessageAudience::class)],
            'body' => ['required', 'string', 'min:1', 'max:3000'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();

        if ($audience === ForumEventMessageAudience::Organizers
            && $event->isOrganizer($actor)
        ) {
            throw ValidationException::withMessages([
                'messageForm.audience' => __('forum_events.validation.organizer_message_audience'),
            ]);
        }

        if (ForumEventMessage::query()
            ->where('sender_user_id', $actor->id)
            ->where('created_at', '>=', now()->subHour())
            ->count() >= 30
        ) {
            throw ValidationException::withMessages([
                'messageForm.body' => __('forum_events.validation.message_rate_limited'),
            ]);
        }

        return DB::transaction(function () use (
            $actor,
            $audience,
            $body,
            $event,
            $idempotencyKey,
        ): ForumEventMessage {
            $message = ForumEventMessage::query()->createOrFirst(
                ['idempotency_key' => $idempotencyKey],
                [
                    'forum_event_id' => $event->id,
                    'sender_user_id' => $actor->id,
                    'stable_key' => 'event-message-'.Str::lower((string) Str::ulid()),
                    'audience' => $audience,
                    'body' => trim($body),
                ],
            );

            if ($message->forum_event_id !== $event->id
                || $message->sender_user_id !== $actor->id
            ) {
                throw ValidationException::withMessages([
                    'messageForm.body' => __('forum_events.validation.idempotency_conflict'),
                ]);
            }

            $this->audit->record(
                event: $event,
                actor: $actor,
                eventType: 'message-sent',
                reasonCode: 'attendee-communication',
                summaryTranslationKey: 'forum_events.history.message_sent',
                metadata: [
                    'message_id' => $message->id,
                    'audience' => $audience->value,
                ],
                idempotencyKey: 'event:message:'.$idempotencyKey,
            );

            return $message;
        }, 3);
    }
}
