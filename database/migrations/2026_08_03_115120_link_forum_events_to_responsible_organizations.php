<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_events', function (Blueprint $table): void {
            $table->foreignId('responsible_organization_id')
                ->nullable()
                ->after('owner_user_id')
                ->constrained('organizations')
                ->restrictOnDelete();
            $table->index(
                ['responsible_organization_id', 'status', 'starts_at', 'id'],
                'forum_events_organization_status_start_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('forum_events', function (Blueprint $table): void {
            $table->dropIndex('forum_events_organization_status_start_idx');
            $table->dropConstrainedForeignId('responsible_organization_id');
        });
    }
};
