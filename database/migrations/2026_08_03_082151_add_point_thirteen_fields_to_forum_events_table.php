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
            $table->foreignId('owner_user_id')
                ->nullable()
                ->after('organizer_user_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('pet_participation_mode', 40)
                ->default('optional')
                ->after('format');
            $table->string('accessibility_status', 40)
                ->default('not_assessed')
                ->after('accessibility_information');
            $table->unsignedInteger('current_version_number')->default(1);
            $table->timestamp('registration_opens_at')->nullable();
            $table->timestamp('registration_closes_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('safety_suspended_at')->nullable();

            $table->index(
                ['owner_user_id', 'status', 'starts_at', 'id'],
                'forum_events_owner_status_start_idx',
            );
            $table->index(
                ['pet_participation_mode', 'status', 'starts_at', 'id'],
                'forum_events_pet_mode_status_start_idx',
            );
            $table->index(
                ['registration_opens_at', 'registration_closes_at', 'id'],
                'forum_events_registration_window_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::withoutForeignKeyConstraints(function (): void {
            Schema::table('forum_events', function (Blueprint $table): void {
                $table->dropIndex('forum_events_owner_status_start_idx');
                $table->dropIndex('forum_events_pet_mode_status_start_idx');
                $table->dropIndex('forum_events_registration_window_idx');
                $table->dropConstrainedForeignId('owner_user_id');
                $table->dropColumn([
                    'pet_participation_mode',
                    'accessibility_status',
                    'current_version_number',
                    'registration_opens_at',
                    'registration_closes_at',
                    'published_at',
                    'safety_suspended_at',
                ]);
            });
        });
    }
};
