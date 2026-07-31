<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumReport;
use App\Models\ForumReportAttachment;
use App\Models\User;

/** @extends ApplicationFactory<ForumReportAttachment> */
final class ForumReportAttachmentFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $contents = fake()->sentence();

        return [
            'forum_report_id' => ForumReport::factory(),
            'uploaded_by_user_id' => User::factory(),
            'disk' => 'local',
            'path' => 'moderation/'.fake()->uuid().'.txt',
            'mime_type' => 'text/plain',
            'size_bytes' => strlen($contents),
            'checksum_sha256' => hash('sha256', $contents),
            'visibility' => 'moderators',
        ];
    }
}
