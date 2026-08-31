<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_moderation_actions', function (Blueprint $table): void {
            $table->index(
                ['target_type', 'target_id', 'reversed_at', 'starts_at', 'ends_at'],
                'forum_moderation_actions_target_active_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('forum_moderation_actions', function (Blueprint $table): void {
            $table->dropIndex('forum_moderation_actions_target_active_idx');
        });
    }
};
