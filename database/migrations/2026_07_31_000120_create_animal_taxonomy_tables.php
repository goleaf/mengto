<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxon_sources', function (Blueprint $table): void {
            $table->id();
            $table->string('stable_key', 120)->unique();
            $table->string('name', 180);
            $table->string('source_type', 60);
            $table->string('version', 120);
            $table->date('release_date')->nullable();
            $table->dateTime('downloaded_at')->nullable();
            $table->string('checksum', 128)->nullable();
            $table->string('license', 180);
            $table->text('attribution');
            $table->string('source_url', 500);
            $table->unsignedSmallInteger('import_priority')->default(100);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('taxon_imports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('taxon_source_id')
                ->constrained('taxon_sources')
                ->restrictOnDelete();
            $table->foreignId('initiated_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('source_version', 120);
            $table->string('state', 40)->default('pending');
            $table->string('checksum', 128);
            $table->unsignedInteger('current_chunk')->default(0);
            $table->unsignedBigInteger('processed_rows')->default(0);
            $table->unsignedBigInteger('inserted_rows')->default(0);
            $table->unsignedBigInteger('updated_rows')->default(0);
            $table->unsignedBigInteger('unchanged_rows')->default(0);
            $table->unsignedBigInteger('synonym_rows')->default(0);
            $table->unsignedBigInteger('archived_rows')->default(0);
            $table->unsignedBigInteger('error_rows')->default(0);
            $table->unsignedBigInteger('warning_rows')->default(0);
            $table->string('resume_token', 500)->nullable();
            $table->json('impact_report')->nullable();
            $table->json('error_report')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['taxon_source_id', 'source_version', 'checksum'],
                'taxon_imports_source_version_checksum_unique',
            );
            $table->index(
                ['taxon_source_id', 'state', 'created_at'],
                'taxon_imports_source_state_created_idx',
            );
        });

        Schema::table('taxon_sources', function (Blueprint $table): void {
            $table->foreignId('active_taxon_import_id')
                ->nullable()
                ->after('is_active')
                ->constrained('taxon_imports')
                ->nullOnDelete();
        });

        Schema::create('taxa', function (Blueprint $table): void {
            $table->id();
            $table->string('stable_key', 160)->unique();
            $table->foreignId('accepted_taxon_id')
                ->nullable()
                ->constrained('taxa')
                ->restrictOnDelete();
            $table->foreignId('original_taxon_id')
                ->nullable()
                ->constrained('taxa')
                ->restrictOnDelete();
            $table->string('resolution_status', 40)->default('accepted');
            $table->boolean('requires_review')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(
                ['accepted_taxon_id', 'resolution_status'],
                'taxa_accepted_resolution_idx',
            );
        });

        Schema::create('taxon_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('taxon_id')
                ->constrained('taxa')
                ->cascadeOnDelete();
            $table->foreignId('taxon_import_id')
                ->constrained('taxon_imports')
                ->cascadeOnDelete();
            $table->foreignId('taxon_source_id')
                ->constrained('taxon_sources')
                ->restrictOnDelete();
            $table->foreignId('parent_taxon_id')
                ->nullable()
                ->constrained('taxa')
                ->restrictOnDelete();
            $table->string('source_record_id', 190);
            $table->string('rank', 80);
            $table->string('scientific_name', 500);
            $table->string('canonical_name', 500);
            $table->string('normalized_scientific_name', 500);
            $table->string('authorship', 500)->nullable();
            $table->string('nomenclatural_code', 80)->nullable();
            $table->string('taxonomic_status', 80);
            $table->unsignedSmallInteger('depth')->default(0);
            $table->text('hierarchy_path')->nullable();
            $table->boolean('is_extinct')->default(false);
            $table->boolean('is_fossil')->default(false);
            $table->boolean('is_marine')->nullable();
            $table->boolean('is_freshwater')->nullable();
            $table->boolean('is_terrestrial')->nullable();
            $table->boolean('has_domestic_relevance')->default(false);
            $table->boolean('has_community_relevance')->default(false);
            $table->boolean('is_active_version')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(
                ['taxon_import_id', 'source_record_id'],
                'taxon_versions_import_source_record_unique',
            );
            $table->unique(
                ['taxon_id', 'taxon_import_id'],
                'taxon_versions_taxon_import_unique',
            );
            $table->index(
                ['parent_taxon_id', 'is_active_version', 'rank'],
                'taxon_versions_parent_active_rank_idx',
            );
            $table->index(
                ['normalized_scientific_name', 'is_active_version'],
                'taxon_versions_scientific_search_idx',
            );
            $table->index(
                ['taxon_source_id', 'source_record_id'],
                'taxon_versions_source_record_idx',
            );
            $table->index(
                ['rank', 'is_active_version'],
                'taxon_versions_rank_active_idx',
            );
        });

        Schema::create('taxon_names', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('taxon_id')
                ->constrained('taxa')
                ->cascadeOnDelete();
            $table->foreignId('taxon_import_id')
                ->nullable()
                ->constrained('taxon_imports')
                ->cascadeOnDelete();
            $table->foreignId('taxon_source_id')
                ->nullable()
                ->constrained('taxon_sources')
                ->nullOnDelete();
            $table->string('locale', 12)->nullable();
            $table->string('language', 80)->nullable();
            $table->string('script', 40)->nullable();
            $table->string('name', 500);
            $table->string('normalized_name', 500);
            $table->string('name_type', 60);
            $table->string('source_record_id', 190)->nullable();
            $table->string('geographic_scope', 180)->nullable();
            $table->boolean('is_preferred')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_local_override')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(
                ['normalized_name', 'locale', 'is_active'],
                'taxon_names_search_locale_active_idx',
            );
            $table->index(
                ['taxon_id', 'locale', 'is_preferred', 'is_active'],
                'taxon_names_preferred_lookup_idx',
            );
        });

        Schema::create('taxon_external_identifiers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('taxon_id')
                ->constrained('taxa')
                ->cascadeOnDelete();
            $table->foreignId('taxon_source_id')
                ->constrained('taxon_sources')
                ->restrictOnDelete();
            $table->string('external_identifier', 190);
            $table->string('identifier_type', 80);
            $table->string('version', 120)->nullable();
            $table->string('external_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(
                ['taxon_source_id', 'external_identifier', 'identifier_type'],
                'taxon_external_ids_source_identifier_type_unique',
            );
        });

        Schema::create('taxon_changes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('taxon_id')
                ->constrained('taxa')
                ->cascadeOnDelete();
            $table->foreignId('taxon_import_id')
                ->nullable()
                ->constrained('taxon_imports')
                ->nullOnDelete();
            $table->foreignId('actor_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('change_type', 60);
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('reason_code', 100);
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(
                ['taxon_id', 'created_at'],
                'taxon_changes_taxon_created_idx',
            );
        });

        Schema::create('breed_registries', function (Blueprint $table): void {
            $table->id();
            $table->string('stable_key', 120)->unique();
            $table->string('name', 180);
            $table->string('jurisdiction', 120)->nullable();
            $table->string('source_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('domestic_classifications', function (Blueprint $table): void {
            $table->id();
            $table->string('stable_key', 160)->unique();
            $table->foreignId('taxon_id')
                ->constrained('taxa')
                ->restrictOnDelete();
            $table->foreignId('breed_registry_id')
                ->nullable()
                ->constrained('breed_registries')
                ->nullOnDelete();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('domestic_classifications')
                ->restrictOnDelete();
            $table->string('classification_type', 60);
            $table->string('canonical_name', 220);
            $table->string('registry_identifier', 160)->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('aliases')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(
                ['taxon_id', 'classification_type', 'is_active'],
                'domestic_classifications_taxon_type_active_idx',
            );
        });

        Schema::create('community_animal_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('stable_key', 140)->unique();
            $table->string('name_translation_key', 190);
            $table->string('description_translation_key', 190);
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_system_managed')->default(true);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('community_animal_group_taxon', function (Blueprint $table): void {
            $table->foreignId('community_animal_group_id')
                ->constrained('community_animal_groups')
                ->cascadeOnDelete();
            $table->foreignId('taxon_id')
                ->constrained('taxa')
                ->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('includes_descendants')->default(true);
            $table->timestamps();

            $table->primary(
                ['community_animal_group_id', 'taxon_id'],
                'community_animal_group_taxon_primary',
            );
        });

        Schema::create('forum_topic_taxon', function (Blueprint $table): void {
            $table->foreignId('forum_topic_id')
                ->constrained('forum_topics')
                ->cascadeOnDelete();
            $table->foreignId('taxon_id')
                ->constrained('taxa')
                ->restrictOnDelete();
            $table->string('context_type', 40)->default('subject');
            $table->json('topic_time_snapshot')->nullable();
            $table->timestamps();

            $table->primary(
                ['forum_topic_id', 'taxon_id', 'context_type'],
                'forum_topic_taxon_primary',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_topic_taxon');
        Schema::dropIfExists('community_animal_group_taxon');
        Schema::dropIfExists('community_animal_groups');
        Schema::dropIfExists('domestic_classifications');
        Schema::dropIfExists('breed_registries');
        Schema::dropIfExists('taxon_changes');
        Schema::dropIfExists('taxon_external_identifiers');
        Schema::dropIfExists('taxon_names');
        Schema::dropIfExists('taxon_versions');
        Schema::dropIfExists('taxa');

        Schema::table('taxon_sources', function (Blueprint $table): void {
            $table->dropForeign(['active_taxon_import_id']);
            $table->dropColumn('active_taxon_import_id');
        });

        Schema::dropIfExists('taxon_imports');
        Schema::dropIfExists('taxon_sources');
    }
};
