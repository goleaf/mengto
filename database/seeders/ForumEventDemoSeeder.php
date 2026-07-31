<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ForumEventInvitationStatus;
use App\Enums\ForumEventRegistrationStatus;
use App\Enums\ForumEventReviewStatus;
use App\Enums\ForumEventUpdateAudience;
use App\Enums\ForumEventUpdateType;
use App\Models\ForumEvent;
use App\Models\ForumEventInvitation;
use App\Models\ForumEventRegistration;
use App\Models\ForumEventReview;
use App\Models\ForumEventUpdate;
use App\Models\User;
use Illuminate\Database\Seeder;
use LogicException;

final class ForumEventDemoSeeder extends Seeder
{
    public function run(): void
    {
        $allowedEnvironments = config('platform.demo_seed_environments');

        if (! is_array($allowedEnvironments) || ! app()->environment($allowedEnvironments)) {
            throw new LogicException('Event demo data may only be created in an explicitly allowed environment.');
        }

        $attendee = User::query()->where('actor_key', 'demo-lithuanian')->first();
        $invitee = User::query()->where('actor_key', 'demo-unverified')->first();
        $walk = ForumEvent::query()->where('stable_key', 'small-dog-social')->first();
        $private = ForumEvent::query()->where('stable_key', 'baxter-birthday')->first();
        $completed = ForumEvent::query()->where('stable_key', 'missing-scout-search')->first();

        if ($attendee !== null && $walk !== null) {
            ForumEventRegistration::query()->updateOrCreate(
                [
                    'forum_event_id' => $walk->id,
                    'user_id' => $attendee->id,
                ],
                [
                    'stable_key' => 'demo-event-registration-lithuanian-walk',
                    'idempotency_key' => 'demo:event:registration:lithuanian-walk',
                    'status' => ForumEventRegistrationStatus::Confirmed,
                    'attendance_format' => 'physical',
                    'guest_count' => 0,
                    'photo_consent' => 'ask_first',
                    'requirements_accepted' => true,
                    'lock_version' => 0,
                ],
            );
            ForumEventUpdate::query()->updateOrCreate(
                ['idempotency_key' => 'demo:event:update:walk-arrival'],
                [
                    'forum_event_id' => $walk->id,
                    'author_user_id' => $walk->organizer_user_id,
                    'stable_key' => 'demo-event-update-walk-arrival',
                    'type' => ForumEventUpdateType::General,
                    'audience' => ForumEventUpdateAudience::Public,
                    'title' => 'Quiet arrival reminder',
                    'body' => 'Please leave extra space between animals while the host checks everyone in.',
                    'published_at' => now(),
                ],
            );
        }

        if ($invitee !== null && $private !== null) {
            ForumEventInvitation::query()->updateOrCreate(
                [
                    'forum_event_id' => $private->id,
                    'invited_user_id' => $invitee->id,
                ],
                [
                    'invited_by_user_id' => $private->organizer_user_id,
                    'stable_key' => 'demo-event-invitation-private',
                    'idempotency_key' => 'demo:event:invitation:private',
                    'status' => ForumEventInvitationStatus::Pending,
                    'expires_at' => now()->addWeeks(2),
                    'responded_at' => null,
                ],
            );
        }

        if ($attendee !== null && $completed !== null) {
            ForumEventRegistration::query()->updateOrCreate(
                [
                    'forum_event_id' => $completed->id,
                    'user_id' => $attendee->id,
                ],
                [
                    'stable_key' => 'demo-event-registration-completed',
                    'idempotency_key' => 'demo:event:registration:completed',
                    'status' => ForumEventRegistrationStatus::CheckedIn,
                    'attendance_format' => 'physical',
                    'guest_count' => 0,
                    'photo_consent' => 'declined',
                    'requirements_accepted' => true,
                    'check_in_method' => 'manual',
                    'checked_in_at' => $completed->starts_at,
                    'lock_version' => 1,
                ],
            );
            ForumEventReview::query()->updateOrCreate(
                [
                    'forum_event_id' => $completed->id,
                    'reviewer_user_id' => $attendee->id,
                ],
                [
                    'stable_key' => 'demo-event-review-completed',
                    'idempotency_key' => 'demo:event:review:completed',
                    'rating' => 5,
                    'title' => 'Clear volunteer coordination',
                    'body' => 'The public search zones and private sighting channel were explained clearly.',
                    'status' => ForumEventReviewStatus::Published,
                ],
            );
        }
    }
}
