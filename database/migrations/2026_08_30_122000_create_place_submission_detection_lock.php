<?php

declare(strict_types=1);

use App\Models\PlaceSubmissionIdentityLock;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const DETECTION_LOCK_HASH = '992a34320277074bc22580f50a8b5e0dfe227340f878c928967833e456a136aa';

    public function up(): void
    {
        PlaceSubmissionIdentityLock::query()->firstOrCreate(
            ['identity_hash' => self::DETECTION_LOCK_HASH],
            ['first_submission_id' => null, 'lock_version' => 0],
        );
    }

    public function down(): void
    {
        PlaceSubmissionIdentityLock::query()->whereKey(self::DETECTION_LOCK_HASH)->delete();
    }
};
