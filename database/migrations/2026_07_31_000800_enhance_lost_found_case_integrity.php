<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('search_cases', function (Blueprint $table): void {
            $table->foreignId('pet_profile_id')
                ->nullable()
                ->after('pet_profile_key')
                ->constrained('pet_profiles')
                ->nullOnDelete();
            $table->foreignId('taxon_id')
                ->nullable()
                ->after('pet_profile_id')
                ->constrained('taxa')
                ->nullOnDelete();
            $table->foreignId('domestic_classification_id')
                ->nullable()
                ->after('taxon_id')
                ->constrained('domestic_classifications')
                ->nullOnDelete();
            $table->foreignId('duplicate_of_search_case_id')
                ->nullable()
                ->after('domestic_classification_id')
                ->constrained('search_cases')
                ->nullOnDelete();
            $table->string('temperament', 300)->nullable()->after('accessories');
            $table->text('animal_snapshot')->nullable()->after('risk_flags');
            $table->boolean('requires_taxonomy_review')->default(false)->after('animal_snapshot');
            $table->boolean('reward_offered')->default(false)->after('contact_token');
            $table->string('reward_summary', 300)->nullable()->after('reward_offered');
            $table->unsignedInteger('lock_version')->default(1)->after('view_count');
            $table->foreignId('reunited_confirmed_by_user_id')
                ->nullable()
                ->after('returned_at')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('reunited_at')->nullable()->after('reunited_confirmed_by_user_id');
            $table->timestamp('archived_at')->nullable()->after('closed_at');

            $table->index(
                ['pet_profile_id', 'status', 'id'],
                'search_cases_pet_status_idx',
            );
            $table->index(
                ['taxon_id', 'status', 'last_seen_at', 'id'],
                'search_cases_taxon_status_seen_idx',
            );
            $table->index(
                ['domestic_classification_id', 'status', 'id'],
                'search_cases_domestic_status_idx',
            );
            $table->index(
                ['duplicate_of_search_case_id', 'status', 'id'],
                'search_cases_duplicate_status_idx',
            );
            $table->index(
                ['archived_at', 'status', 'id'],
                'search_cases_archive_status_idx',
            );
            $table->index(
                ['reunited_confirmed_by_user_id', 'status', 'id'],
                'search_cases_reunion_user_status_idx',
            );
        });

        Schema::table('search_reports', function (Blueprint $table): void {
            $table->foreignId('forum_report_id')
                ->nullable()
                ->after('sighting_id')
                ->constrained('forum_reports')
                ->nullOnDelete();
            $table->index(
                ['forum_report_id', 'status', 'id'],
                'search_reports_forum_status_idx',
            );
        });

        Schema::create('search_case_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('search_case_id')->constrained()->restrictOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 80);
            $table->string('previous_status', 40)->nullable();
            $table->string('current_status', 40)->nullable();
            $table->string('reason_translation_key', 190);
            $table->uuid('idempotency_key')->unique();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(
                ['search_case_id', 'created_at', 'id'],
                'search_case_events_case_created_idx',
            );
            $table->index(
                ['event_type', 'created_at', 'id'],
                'search_case_events_type_created_idx',
            );
            $table->index(
                ['actor_user_id', 'created_at', 'id'],
                'search_case_events_actor_created_idx',
            );
        });

        Schema::create('search_contact_relays', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('search_case_id')->constrained()->restrictOnDelete();
            $table->foreignId('sender_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('recipient_user_id')->constrained('users')->restrictOnDelete();
            $table->uuid('idempotency_key')->unique();
            $table->string('purpose', 40);
            $table->text('message');
            $table->string('status', 30)->default('submitted');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(
                ['search_case_id', 'status', 'created_at', 'id'],
                'search_contact_relays_case_status_idx',
            );
            $table->index(
                ['recipient_user_id', 'status', 'created_at', 'id'],
                'search_contact_relays_recipient_status_idx',
            );
            $table->index(
                ['sender_user_id', 'status', 'created_at', 'id'],
                'search_contact_relays_sender_status_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('search_contact_relays', function (Blueprint $table): void {
            $table->dropIndex('search_contact_relays_sender_status_idx');
        });
        Schema::table('search_case_events', function (Blueprint $table): void {
            $table->dropIndex('search_case_events_actor_created_idx');
        });
        Schema::dropIfExists('search_contact_relays');
        Schema::dropIfExists('search_case_events');

        Schema::table('search_reports', function (Blueprint $table): void {
            $table->dropIndex('search_reports_forum_status_idx');
            $table->dropConstrainedForeignId('forum_report_id');
        });

        Schema::table('search_cases', function (Blueprint $table): void {
            $table->dropIndex('search_cases_pet_status_idx');
            $table->dropIndex('search_cases_taxon_status_seen_idx');
            $table->dropIndex('search_cases_domestic_status_idx');
            $table->dropIndex('search_cases_duplicate_status_idx');
            $table->dropIndex('search_cases_archive_status_idx');
            $table->dropIndex('search_cases_reunion_user_status_idx');
            $table->dropConstrainedForeignId('reunited_confirmed_by_user_id');
            $table->dropConstrainedForeignId('duplicate_of_search_case_id');
            $table->dropConstrainedForeignId('domestic_classification_id');
            $table->dropConstrainedForeignId('taxon_id');
            $table->dropConstrainedForeignId('pet_profile_id');
            $table->dropColumn([
                'animal_snapshot',
                'temperament',
                'requires_taxonomy_review',
                'reward_offered',
                'reward_summary',
                'lock_version',
                'reunited_at',
                'archived_at',
            ]);
        });
    }
};
