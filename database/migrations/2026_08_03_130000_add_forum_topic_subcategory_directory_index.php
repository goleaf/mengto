<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_topics', function (Blueprint $table): void {
            $table->index(
                ['subcategory', 'status', 'last_activity_at', 'id'],
                'forum_topics_subcategory_status_activity_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('forum_topics', function (Blueprint $table): void {
            $table->dropIndex('forum_topics_subcategory_status_activity_idx');
        });
    }
};
