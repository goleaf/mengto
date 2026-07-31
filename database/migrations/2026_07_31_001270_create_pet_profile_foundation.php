<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pet_profiles', function (Blueprint $table): void {
            $table->foreignId('canonical_profile_id')
                ->nullable()
                ->after('user_id')
                ->constrained('pet_profiles')
                ->restrictOnDelete();
            $table->foreignId('taxon_id')
                ->nullable()
                ->after('species')
                ->constrained('taxa')
                ->nullOnDelete();
            $table->foreignId('domestic_classification_id')
                ->nullable()
                ->after('breed')
                ->constrained('domestic_classifications')
                ->nullOnDelete();
            $table->string('creation_key', 190)->nullable()->unique();
            $table->string('creator_relationship', 40)->nullable();
            $table->string('birth_date_precision', 24)->default('unknown');
            $table->string('sex', 32)->default('unknown');
            $table->string('reproductive_status', 40)->default('unknown');
            $table->boolean('is_discoverable')->default(true);
            $table->boolean('allow_external_indexing')->default(false);
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamp('state_entered_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('hidden_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamp('memorialized_at')->nullable();
            $table->timestamp('deletion_requested_at')->nullable();
            $table->timestamp('deletion_scheduled_for')->nullable();
            $table->timestamp('merged_at')->nullable();

            $table->index(
                ['canonical_profile_id', 'status', 'id'],
                'pet_profiles_canonical_status_idx',
            );
            $table->index(
                ['taxon_id', 'status', 'id'],
                'pet_profiles_taxon_status_idx',
            );
            $table->index(
                ['domestic_classification_id', 'status', 'id'],
                'pet_profiles_domestic_status_idx',
            );
            $table->index(
                ['is_discoverable', 'visibility', 'status', 'id'],
                'pet_profiles_discovery_idx',
            );
            $table->index(
                ['deletion_scheduled_for', 'status', 'id'],
                'pet_profiles_deletion_schedule_idx',
            );
        });

        Schema::create('pet_profile_managers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pet_profile_id')
                ->constrained('pet_profiles')
                ->restrictOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('actor_key_snapshot', 120);
            $table->string('role', 40);
            $table->string('status', 24)->default('invited');
            $table->json('permission_overrides')->nullable();
            $table->string('evidence_status', 24)->default('unverified');
            $table->text('evidence_summary')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('invited_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('revoked_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->unsignedInteger('lock_version')->default(1);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(
                ['pet_profile_id', 'user_id'],
                'pet_profile_managers_profile_user_unique',
            );
            $table->index(
                ['pet_profile_id', 'status', 'ends_at', 'id'],
                'pet_profile_managers_profile_access_idx',
            );
            $table->index(
                ['user_id', 'status', 'ends_at', 'id'],
                'pet_profile_managers_user_access_idx',
            );
            $table->index('invited_by_user_id', 'pet_profile_managers_inviter_idx');
            $table->index('revoked_by_user_id', 'pet_profile_managers_revoker_idx');
        });

        Schema::create('pet_profile_privacy_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pet_profile_id')
                ->unique()
                ->constrained('pet_profiles')
                ->cascadeOnDelete();
            $table->string('profile_visibility', 24)->default('private');
            $table->json('section_rules')->nullable();
            $table->boolean('is_discoverable')->default(false);
            $table->boolean('allow_external_indexing')->default(false);
            $table->boolean('allow_direct_link')->default(false);
            $table->string('owner_display_mode', 32)->default('contact-button');
            $table->string('manager_display_mode', 32)->default('hidden');
            $table->string('public_location_precision', 24)->default('hidden');
            $table->unsignedInteger('lock_version')->default(1);
            $table->foreignId('updated_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->index(
                ['is_discoverable', 'profile_visibility', 'pet_profile_id'],
                'pet_profile_privacy_discovery_idx',
            );
            $table->index('updated_by_user_id', 'pet_profile_privacy_updater_idx');
        });

        Schema::create('pet_profile_lifecycle_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pet_profile_id')
                ->constrained('pet_profiles')
                ->restrictOnDelete();
            $table->foreignId('actor_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('manager_id')
                ->nullable()
                ->constrained('pet_profile_managers')
                ->nullOnDelete();
            $table->string('actor_key_snapshot', 120);
            $table->string('actor_role_snapshot', 40)->nullable();
            $table->string('event_type', 60);
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40)->nullable();
            $table->string('reason_code', 100);
            $table->string('reason_translation_key', 190)->nullable();
            $table->unsignedInteger('lock_version');
            $table->string('idempotency_key', 190)->nullable()->unique();
            $table->json('public_metadata')->nullable();
            $table->text('private_metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(
                ['pet_profile_id', 'occurred_at', 'id'],
                'pet_profile_events_profile_time_idx',
            );
            $table->index(
                ['event_type', 'occurred_at', 'id'],
                'pet_profile_events_type_time_idx',
            );
            $table->index('actor_user_id', 'pet_profile_events_actor_idx');
            $table->index('manager_id', 'pet_profile_events_manager_idx');
        });

        Schema::create('pet_profile_slug_aliases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pet_profile_id')
                ->constrained('pet_profiles')
                ->cascadeOnDelete();
            $table->string('slug', 120);
            $table->string('source', 32)->default('profile');
            $table->boolean('is_active')->default(true);
            $table->timestamp('retired_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['pet_profile_id', 'slug'],
                'pet_profile_slug_aliases_profile_slug_unique',
            );
            $table->index(
                ['slug', 'is_active', 'id'],
                'pet_profile_slug_aliases_lookup_idx',
            );
        });

        Schema::create('pet_profile_facts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pet_profile_id')
                ->constrained('pet_profiles')
                ->restrictOnDelete();
            $table->string('fact_key', 80);
            $table->text('value');
            $table->string('normalized_value_hash', 64)->nullable();
            $table->string('precision', 24)->default('unknown');
            $table->string('source_type', 40)->default('owner');
            $table->string('source_reference', 190)->nullable();
            $table->foreignId('author_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('verification_status', 24)->default('unverified');
            $table->string('visibility', 24)->default('private');
            $table->boolean('is_current')->default(true);
            $table->string('current_key', 190)->nullable()->unique();
            $table->foreignId('replaces_fact_id')
                ->nullable()
                ->constrained('pet_profile_facts')
                ->restrictOnDelete();
            $table->timestamp('recorded_at');
            $table->timestamp('retired_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(
                ['pet_profile_id', 'fact_key', 'is_current', 'id'],
                'pet_profile_facts_profile_key_current_idx',
            );
            $table->index(
                ['normalized_value_hash', 'fact_key', 'is_current'],
                'pet_profile_facts_hash_key_current_idx',
            );
            $table->index('author_user_id', 'pet_profile_facts_author_idx');
            $table->index('replaces_fact_id', 'pet_profile_facts_replaces_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pet_profile_facts');
        Schema::dropIfExists('pet_profile_slug_aliases');
        Schema::dropIfExists('pet_profile_lifecycle_events');
        Schema::dropIfExists('pet_profile_privacy_settings');
        Schema::dropIfExists('pet_profile_managers');

        Schema::table('pet_profiles', function (Blueprint $table): void {
            $table->dropIndex('pet_profiles_deletion_schedule_idx');
            $table->dropIndex('pet_profiles_discovery_idx');
            $table->dropIndex('pet_profiles_domestic_status_idx');
            $table->dropIndex('pet_profiles_taxon_status_idx');
            $table->dropIndex('pet_profiles_canonical_status_idx');
            $table->dropUnique(['creation_key']);
            $table->dropConstrainedForeignId('domestic_classification_id');
            $table->dropConstrainedForeignId('taxon_id');
            $table->dropConstrainedForeignId('canonical_profile_id');
            $table->dropColumn([
                'creation_key',
                'creator_relationship',
                'birth_date_precision',
                'sex',
                'reproductive_status',
                'is_discoverable',
                'allow_external_indexing',
                'lock_version',
                'state_entered_at',
                'published_at',
                'hidden_at',
                'archived_at',
                'memorialized_at',
                'deletion_requested_at',
                'deletion_scheduled_for',
                'merged_at',
            ]);
        });
    }
};
