<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumReport;
use App\Models\ForumReportEvent;
use App\Models\User;

/** @extends ApplicationFactory<ForumReportEvent> */
final class ForumReportEventFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'forum_report_id' => ForumReport::factory(),
            'actor_user_id' => User::factory(),
            'event_type' => 'submitted',
            'to_status' => 'submitted',
            'user_message_translation_key' => 'forum_moderation.messages.report_submitted',
            'metadata' => ['source' => 'factory', 'version' => 1],
        ];
    }
}
