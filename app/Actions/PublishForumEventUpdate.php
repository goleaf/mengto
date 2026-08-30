<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumEventUpdateAudience;
use App\Enums\ForumEventUpdateType;
use App\Models\ForumEventRegistration;
use App\Models\ForumEvent;
use App\Models\ForumEventUpdate;
use App\Models\User;
use App\Services\ForumEventAudit;
use App\Services\ForumEventNotifier;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class PublishForumEventUpdate
{
    public function __construct(
        private Gate $gate,
        private ForumEventAudit $audit,
        private ForumEventNotifier $notifier,
    ) {}

    public function handle(
        User $actor,
        ForumEvent $event,
        ForumEventUpdateType $type,
        ForumEventUpdateAudience $audience,
        string $title,
        string $body,
        string $idempotencyKey,
    ): ForumEventUpdate {
        $this->gate->forUser($actor)->authorize('publishUpdate', $event);
        Validator::make([
            'type' => $type->value,
            'audience' => $audience->value,
            'title' => $title,
            'body' => $body,
            'idempotency_key' => $idempotencyKey,
        ], [
            'type' => ['required', Rule::enum(ForumEventUpdateType::class)],
            'audience' => ['required', Rule::enum(ForumEventUpdateAudience::class)],
            'title' => ['required', 'string', 'min:4', 'max:180'],
            'body' => ['required', 'string', 'min:10', 'max:10000'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();

        $update = DB::transaction(function () use (
            $actor,
            $audience,
            $body,
            $event,
            $idempotencyKey,
            $title,
            $type,
        ): ForumEventUpdate {
            $update = ForumEventUpdate::query()->createOrFirst(
                ['idempotency_key' => $idempotencyKey],
                [
                    'forum_event_id' => $event->id,
                    'author_user_id' => $actor->id,
                    'stable_key' => 'event-update-'.Str::lower((string) Str::ulid()),
                    'type' => $type,
                    'audience' => $audience,
                    'title' => trim($title),
                    'body' => trim($body),
                    'published_at' => now(),
                ],
            );

            if ($update->forum_event_id !== $event->id
                || $update->author_user_id !== $actor->id
            ) {
                throw ValidationException::withMessages([
                    'updateForm.title' => __('forum_events.validation.idempotency_conflict'),
                ]);
            }

            $this->audit->record(
                event: $event,
                actor: $actor,
                eventType: 'update-published',
                reasonCode: $type->value,
                summaryTranslationKey: 'forum_events.history.update_published',
                metadata: [
                    'update_id' => $update->id,
                    'audience' => $audience->value,
                ],
                idempotencyKey: 'event:update:'.$idempotencyKey,
            );

            return $update;
        }, 3);

        ForumEventRegistration::query()
            ->where('forum_event_id', $event->id)
            ->whereIn('status', ForumEvent::participantAccessStatusValues())
            ->with('user:id,actor_key,locale')
            ->orderBy('id')
            ->chunkById(100, function ($registrations) use ($event, $update): void {
                foreach ($registrations as $registration) {
                    $this->notifier->send(
                        $registration->user,
                        $event,
                        'event-organizer-update',
                        'forum_events.notifications.material_update_title',
                        'forum_events.notifications.material_update_body',
                        'event-organizer-update:'.$update->id.':'.$registration->user_id,
                    );
                }
            });

        return $update;
    }
}
