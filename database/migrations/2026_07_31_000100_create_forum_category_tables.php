<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('forum_categories')
                ->restrictOnDelete();
            $table->string('stable_key', 160)->unique();
            $table->string('slug', 160)->unique();
            $table->string('icon', 80)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->string('visibility', 40)->default('public');
            $table->string('moderation_level', 40)->default('standard');
            $table->unsignedSmallInteger('schema_version')->default(1);
            $table->boolean('is_system_managed')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('rules')->nullable();
            $table->json('permissions')->nullable();
            $table->json('topic_type_keys')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(
                ['parent_id', 'is_active', 'position'],
                'forum_categories_parent_active_position_idx',
            );
            $table->index(
                ['is_system_managed', 'is_active'],
                'forum_categories_managed_active_idx',
            );
        });

        Schema::create('forum_category_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_category_id')
                ->constrained('forum_categories')
                ->cascadeOnDelete();
            $table->string('locale', 12);
            $table->string('name', 180);
            $table->text('description')->nullable();
            $table->text('notice')->nullable();
            $table->text('rules_summary')->nullable();
            $table->boolean('is_reviewed')->default(false);
            $table->timestamps();

            $table->unique(
                ['forum_category_id', 'locale'],
                'forum_category_translations_category_locale_unique',
            );
            $table->index(
                ['locale', 'name'],
                'forum_category_translations_locale_name_idx',
            );
        });

        Schema::create('forum_category_aliases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_category_id')
                ->constrained('forum_categories')
                ->cascadeOnDelete();
            $table->string('locale', 12)->nullable();
            $table->string('alias', 180);
            $table->string('normalized_alias', 180);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(
                ['forum_category_id', 'locale', 'normalized_alias'],
                'forum_category_aliases_owner_locale_alias_unique',
            );
            $table->index(
                ['locale', 'normalized_alias', 'is_active'],
                'forum_category_aliases_lookup_idx',
            );
        });

        Schema::create('forum_category_redirects', function (Blueprint $table): void {
            $table->id();
            $table->string('source_slug', 160)->unique();
            $table->foreignId('target_forum_category_id')
                ->constrained('forum_categories')
                ->restrictOnDelete();
            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('reason_code', 80);
            $table->boolean('is_permanent')->default(true);
            $table->timestamps();
        });

        Schema::create('forum_category_relations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_category_id')
                ->constrained('forum_categories')
                ->cascadeOnDelete();
            $table->foreignId('related_forum_category_id')
                ->constrained('forum_categories')
                ->cascadeOnDelete();
            $table->string('relation_type', 40)->default('related');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(
                ['forum_category_id', 'related_forum_category_id', 'relation_type'],
                'forum_category_relations_pair_type_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_category_relations');
        Schema::dropIfExists('forum_category_redirects');
        Schema::dropIfExists('forum_category_aliases');
        Schema::dropIfExists('forum_category_translations');
        Schema::dropIfExists('forum_categories');
    }
};
